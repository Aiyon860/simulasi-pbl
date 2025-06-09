<?php
namespace App\Http\Controllers;

use App\Models\Kurir;
use App\Models\Barang;
use App\Models\Status;
use App\Models\SatuanBerat;
use App\Models\CabangKeToko;
use App\Models\DetailGudang;
use Illuminate\Http\Request;
use App\Models\GudangDanToko;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\StatusResource;
use App\Http\Resources\TokoCreateResource;
use App\Http\Resources\KurirCreateResource;
use App\Http\Resources\BarangCreateResource;
use App\Http\Resources\CabangCreateResource;
use App\Helpers\ShippingAndReturnCodeHelpers;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\CabangKeTokoShowResource;
use App\Http\Resources\CabangKeTokoIndexResource;
use App\Http\Resources\SatuanBeratCreateResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CabangKeTokoController extends Controller
{
    public function index()
    {
        try {
            $cabangKeToko = CabangKeToko::select([
                'id', 'kode', 'id_cabang',
                'id_toko', 'id_barang', 'id_satuan_berat',
                'id_kurir', 'id_status', 'berat_satuan_barang',
                'jumlah_barang', 'tanggal'
            ])->with([
                'cabang:id,nama_gudang_toko,alamat,no_telepon',
                'toko:id,nama_gudang_toko,alamat,no_telepon',
                'barang:id,nama_barang',
                'kurir:id,nama_kurir',
                'satuanBerat:id,nama_satuan_berat',
                'status:id,nama_status'
            ])
            ->where('flag', 1)
            ->orderBy('tanggal', 'desc')
            ->get();

            $statuses = Status::select(['id', 'nama_status'])->get();
            
            $headings = [
                'ID',
                'Nama Barang',
                'Tujuan',
                'Jumlah Barang',
                'Tanggal',
                'Status',
            ];
            
            return response()->json([
                'status' => true,
                'message' => 'Data Cabang Ke Toko',
                'data' => [
                    'cabangKeTokos' => CabangKeTokoIndexResource::collection($cabangKeToko),
                    'statuses' => StatusResource::collection($statuses),
                    
                    /** @var array<int, string> */
                    'headings' => $headings,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data Cabang Ke Toko.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function create()
    {
        try {
            $barang = Barang::select(['id', 'nama_barang'])
                ->where('flag', 1)
                ->get();
            $satuanBerat = SatuanBerat::select(['id', 'nama_satuan_berat'])->get();
            $kurir = Kurir::select(['id', 'nama_kurir'])->get();

            // Query builder menjadi immutable, maka harus mengclone base query builder nya
            $gudangDanToko = GudangDanToko::select(['id', 'nama_gudang_toko', 'kategori_bangunan'])
                ->where('id', '!=', 1)
                ->where('kategori_bangunan', '!=', '1')
                ->where('flag', 1);
            $cabang = (clone $gudangDanToko)->where('kategori_bangunan', '=', 0)->get();
            $toko = (clone $gudangDanToko)->where('kategori_bangunan', '=', 2)->get();

            return response()->json([
                'status' => true,
                'message' => 'Data Barang Cabang ke Toko',
                'data' => [
                    'barang' => BarangCreateResource::collection($barang),
                    'satuanBerat' => SatuanBeratCreateResource::collection($satuanBerat),
                    'kurir' => KurirCreateResource::collection($kurir),
                    'cabang' => CabangCreateResource::collection($cabang),
                    'toko' => TokoCreateResource::collection($toko),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data untuk form Cabang ke Toko.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_cabang' => 'required|exists:gudang_dan_tokos,id',
                'id_toko' => 'required|exists:gudang_dan_tokos,id',
                'id_barang' => 'required|exists:barangs,id',
                'id_satuan_berat' => 'required|exists:satuan_berats,id',
                'id_kurir' => 'required|exists:kurirs,id',
                'berat_satuan_barang' => 'required|numeric|min:1',
                'jumlah_barang' => 'required|integer|min:1',
            ]);

            $barang = DetailGudang::where('id_gudang', $request->id_cabang)
                ->where('id_barang', $request->id_barang)
                ->first();

            if (!$barang || $barang->jumlah_stok < $request->jumlah_barang) {
                return response()->json([
                    'status' => false,
                    'message' => 'Jumlah stok tidak mencukupi untuk dikirim.',
                ], 409);
            }

            $currentTime = now();

            $cabangKeToko = array_merge($validated, [
                'kode' => ShippingAndReturnCodeHelpers::generateCabangKeTokoCode($currentTime),
                'id_status' => 1,
                'tanggal' => $currentTime,
            ]);

            DB::transaction(function () use ($cabangKeToko) {
                CabangKeToko::create($cabangKeToko);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

            return response()->json([
                'status' => true,
                'message' => 'Barang berhasil terkirim ke Toko',
            ], 201); // Created
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan tidak valid.',
                'error' => $e->getMessage(),
            ], 422); // Unprocessable Entity
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengirimkan barang. Silakan coba lagi.',
                'error' => $th->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    public function show(string $id)
    {
        try {
            $CabangKeToko = CabangKeToko::with([
                'cabang:id,nama_gudang_toko,alamat,no_telepon',
                'toko:id,nama_gudang_toko,alamat,no_telepon',
                'barang:id,nama_barang',
                'kurir:id,nama_kurir',
                'satuanBerat:id,nama_satuan_berat',
                'status:id,nama_status'
            ])->findOrFail($id, [
                'id', 'kode', 'id_cabang',
                'id_toko', 'id_barang', 'id_satuan_berat',
                'id_kurir', 'id_status', 'berat_satuan_barang',
                'jumlah_barang', 'tanggal'
            ]);

            return response()->json([
                'status' => true,
                'message' => "Data Cabang Ke Toko dengan ID: {$id}",
                'data' => new CabangKeTokoShowResource($CabangKeToko),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Cabang Ke Toko dengan ID: {$id} tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404); // Not Found
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil data Cabang Ke Toko dengan ID: {$id}.",
                'error' => $th->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'id_status' => 'required|exists:statuses,id',
            ]);

            $cabangKeToko = CabangKeToko::findOrFail($id);

            if ($cabangKeToko->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Data Pengiriman dari Cabang Ke Toko dengan ID: {$id} sudah dihapus sebelumnya.",
                ], 409); // Conflict
            }

            $cabangKeToko->update($validated);

            return response()->json([
                'status' => true,
                'message' => "Berhasil memperbarui status pengiriman dari Cabang Ke Toko dengan ID: {$id}",
                'data' => new CabangKeTokoIndexResource($cabangKeToko),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Pengiriman dari Cabang Ke Toko dengan ID: {$id} tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan tidak valid.',
                'error' => $e->getMessage()
            ], 422); // Unprocessable Entity
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat memperbarui status pengiriman dari Cabang Ke Toko dengan ID: {$id}.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $CabangKeToko = CabangKeToko::findOrFail($id);

            if ($CabangKeToko->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Data Cabang Ke Toko dengan ID: {$id} sudah dihapus sebelumnya",
                ], 400); // Bad Request
            }

            DB::transaction(function () use ($CabangKeToko) {
                $CabangKeToko->update(['flag' => 0]);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

            return response()->json([
                'status' => true,
                'message' => "Berhasil menghapus Data Cabang Ke Toko dengan ID: {$id}",
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Cabang Ke Toko dengan ID: {$id} tidak ditemukan.",
            ], 404); // Not Found
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Gagal menghapus Data Cabang Ke Toko dengan ID: {$id}.",
                'error' => $th->getMessage(),
            ], 500); // Internal Server Error
        }
    }
}
