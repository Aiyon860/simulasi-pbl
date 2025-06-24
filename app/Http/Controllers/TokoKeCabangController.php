<?php

namespace App\Http\Controllers;

use App\Models\Kurir;
use App\Models\Barang;
use App\Models\Status;
use App\Models\SatuanBerat;
use App\Helpers\CodeHelpers;
use App\Models\DetailGudang;
use App\Models\TokoKeCabang;
use Illuminate\Http\Request;
use App\Models\GudangDanToko;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\StatusResource;
use App\Http\Resources\TokoCreateResource;
use App\Http\Resources\KurirCreateResource;
use App\Http\Resources\BarangCreateResource;
use App\Http\Resources\CabangCreateResource;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\SatuanBeratCreateResource;
use App\Http\Resources\TokoKeCabangIndexResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;;

class TokoKeCabangController extends Controller
{
    public function index(Request $request)
    {
        try {
            $TokoKeCabang = TokoKeCabang::select([
                'id', 'kode', 'id_cabang',
                'id_toko', 'id_barang', 'id_satuan_berat',
                'id_kurir', 'id_status', 'berat_satuan_barang',
                'jumlah_barang', 'tanggal', 'id_verifikasi',
            ])->with([
                'cabang:id,nama_gudang_toko,alamat,no_telepon',
                'toko:id,nama_gudang_toko,alamat,no_telepon',
                'barang:id,nama_barang',
                'kurir:id,nama_kurir',
                'satuanBerat:id,nama_satuan_berat',
                'status:id,nama_status',
                'verifikasi:id,jenis_verifikasi'
            ])->where('flag', 1)
            ->orderBy('tanggal', 'desc')
            ->get();

            $statuses = Status::select(['id', 'nama_status'])->get();
            $opname = $request->attributes->get('opname_status');

            $headings = $TokoKeCabang->isEmpty() ? [] : array_keys($TokoKeCabang->first()->getAttributes());
            $headings = array_map(function ($heading) {
                return str_replace('_', ' ', ucfirst($heading));
            }, $headings);

            return response()->json([
                'status' => true,
                'message' => 'Data Toko Ke Cabang',
                'data' => [
                    'TokoKeCabangs' => TokoKeCabangIndexResource::collection($TokoKeCabang),
                    'statuses' => StatusResource::collection($statuses),
                    'status_opname' => $opname,

                    /** @var array<int, string> */
                    'headings' => $headings,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data Toko Ke Cabang.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function create()
    {
        try {
            $barang = Barang::select(['id', 'nama_barang'])
                ->where('flag', '=', 1)
                ->get();
            $satuanBerat = SatuanBerat::select(['id', 'nama_satuan_berat'])->get();
            $kurir = Kurir::select(['id', 'nama_kurir'])->get();

            // Query builder menjadi immutable, maka harus mengclone base query builder nya
            $gudangDanToko = GudangDanToko::select(['id', 'nama_gudang_toko', 'kategori_bangunan'])
                ->where('id', '!=', 1)
                ->where('kategori_bangunan', '!=', '1')
                ->where('flag', '=', 1);
            $cabang = (clone $gudangDanToko)->where('kategori_bangunan', '=', 0)->get();
            $toko = (clone $gudangDanToko)->where('kategori_bangunan', '=', 2)->get();

            return response()->json([
                'status' => true,
                'message' => 'Data pendukung untuk form Toko Ke Cabang.',
                'data' => [
                    'barang' => BarangCreateResource::collection($barang),
                    'satuanBerat' => SatuanBeratCreateResource::collection($satuanBerat),
                    'kurir' => KurirCreateResource::collection($kurir),
                    'toko' => TokoCreateResource::collection($toko),
                    'cabang' => CabangCreateResource::collection($cabang),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menyiapkan data untuk form Toko Ke Cabang.',
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
                'id_kurir' => 'nullable|exists:kurirs,id',
                'jumlah_barang' => 'required|integer|min:1',
            ]);

            $barang = DetailGudang::where('id_gudang', $request->id_toko)   // gudang pusat
                ->where('id_barang', $request->id_barang)
                ->firstOrFail(['jumlah_stok']);

            if ($barang->jumlah_stok < $request->jumlah_barang) {
                $namaBarang = $barang?->barang?->nama_barang ?? 'Barang tidak ditemukan';
                $stokTersedia = $barang?->jumlah_stok ?? 0;
                return response()->json([
                    'status' => false,
                    'message' => "Stok untuk barang {$namaBarang} tidak mencukupi. Diminta: {$request->jumlah_barang}, Tersedia: $stokTersedia.",
                ], 409);
            }

            $barangGeneral = Barang::findOrFail($request->id_barang, [
                'id', 'id_satuan_berat', 'berat_satuan_barang'
            ]);

            $currentTime = now();

            $tokoKeCabang = array_merge($validated, [
                'kode' => CodeHelpers::generateTokoKeCabangCode($currentTime),
                'id_status' => 1,
                'id_satuan_berat' => $barangGeneral->id_satuan_berat,
                'berat_satuan_barang' => $barangGeneral->berat_satuan_barang,
                'tanggal' => $currentTime,
            ]);

            DB::transaction(function () use ($tokoKeCabang) {
                $tokoKeCabang = TokoKeCabang::create($tokoKeCabang);
            }, 3);

            return response()->json([
                'status' => true,
                'message' => 'Retur berhasil dikirim dari Toko ke Cabang.',
            ], 201); // 201 Created
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan tidak valid.',
                'errors' => $e->errors(),
            ], 422); // Unprocessable Entity
        } catch (\Throwable $th) { // Using Throwable for broader error catching
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengirimkan barang Dari Toko ke Cabang. Silakan coba lagi.',
                'error' => $th->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    public function show(string $id)
    {
        try {
            $tokoKeCabang = TokoKeCabang::with([
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
                'message' => "Detail Data Toko Ke Cabang dengan kode: {$tokoKeCabang->kode}.",
                'data' => new TokoKeCabangIndexResource($tokoKeCabang),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Toko Ke Cabang yang dicari tidak ditemukan.",
            ], 404); // Not Found
        } catch (\Exception $e) { // Catching a general Exception for other errors
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil detail data Toko Ke Cabang.",
                'error' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'id_status' => 'nullable|exists:statuses,id',
                'id_verifikasi' => 'nullable|exists:verifikasi,id',
            ]);

            $TokoKeCabang = TokoKeCabang::with([
                'cabang:id,nama_gudang_toko,alamat,no_telepon',
                'toko:id,nama_gudang_toko,alamat,no_telepon',
                'barang:id,nama_barang',
                'kurir:id,nama_kurir',
                'satuanBerat:id,nama_satuan_berat',
                'status:id,nama_status',
                'verifikasi:id,jenis_verifikasi'
            ])->findOrFail($id, [
                'id', 'kode', 'id_cabang',
                'id_toko', 'id_barang', 'id_satuan_berat',
                'id_kurir', 'id_status', 'berat_satuan_barang',
                'jumlah_barang', 'tanggal', 'id_verifikasi'
            ]);

            DB::transaction(function () use ($validated, $TokoKeCabang) {
                $TokoKeCabang->update($validated);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

            return response()->json([
                'status' => true,
                'message' => "Data Toko ke Cabang dengan kode: {$TokoKeCabang->kode} berhasil diperbarui",
                'data' => new TokoKeCabangIndexResource($TokoKeCabang),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan tidak valid.',
                'errors' => $e->getMessage(),
            ], 422); // Unprocessable Entity
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Toko ke Cabang yang dicari tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404); // Not Found
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memperbarui data Toko ke Cabang. Silakan coba lagi.',
                'error' => $th->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    public function destroy(string $id)
    {
        try {
            $tokoKeCabang = TokoKeCabang::with([
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

            if ($tokoKeCabang->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Data Toko Ke Cabang dengan kode: {$tokoKeCabang->kode} sudah dihapus.",
                ], 409); // Conflict status code for already deleted
            }

            DB::transaction(function () use ($tokoKeCabang) {
                $tokoKeCabang->update(['flag' => 0]);
            }, 3);

            return response()->json([
                'status' => true,
                'message' => "Data Toko Ke Cabang dengan kode: {$tokoKeCabang->kode} berhasil dihapus (dinonaktifkan).",
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Toko Ke Cabang yang dicari tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404); // Not Found
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Gagal menghapus Data Toko Ke Cabang.",
                'error' => $th->getMessage(),
            ], 500); // Internal Server Error
        }
    }
}
