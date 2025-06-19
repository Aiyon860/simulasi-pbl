<?php
namespace App\Http\Controllers;
use App\Models\Kurir;
use App\Models\Barang;
use App\Models\Status;
use App\Models\SatuanBerat;
use App\Models\DetailGudang;
use Illuminate\Http\Request;
use App\Models\GudangDanToko;
use App\Models\PusatKeCabang;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\StatusResource;
use App\Http\Resources\KurirCreateResource;
use App\Http\Resources\BarangCreateResource;
use App\Http\Resources\CabangCreateResource;
use App\Helpers\CodeHelpers;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\PusatKeCabangShowResource;
use App\Http\Resources\SatuanBeratCreateResource;
use App\Http\Resources\PusatKeCabangIndexResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PusatKeCabangController extends Controller
{
    public function index(Request $request)
    {
        try {
            $pusatKeCabang = PusatKeCabang::select([
                'id', 'kode', 'id_barang',
                'id_cabang', 'jumlah_barang', 
                'tanggal', 'id_status',
            ])->with([
                'cabang:id,nama_gudang_toko', 
                'barang:id,nama_barang',
                'status:id,nama_status'
            ])->where('flag', '=', 1)
            ->get();

            $statuses = Status::select(['id', 'nama_status'])->get();
            $opname = $request->attributes->get('opname_status');

            $headings = [
                'ID',
                'Nama Barang',
                'Tujuan',
                'Jumlah Barang',
                'Tanggal',
                'Status',
            ];

            return response()->json([
                'status'=> true,
                'message'=> 'Data transaksi pengiriman barang dari pusat ke cabang',
                'data'=> [
                    'pusatKeCabangs' => PusatKeCabangIndexResource::collection($pusatKeCabang),
                    'statuses' => StatusResource::collection($statuses),
                    'status_opname' => $opname,

                    /** @var array<int, string> */
                    'headings' => $headings,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data pengiriman dari pusat ke cabang.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function create()
    {
        try {
            $barangs = Barang::select(['id', 'nama_barang', 'id_satuan_berat'])
                ->with('satuanBerat:id,nama_satuan_berat')
                ->get();
            $cabang = GudangDanToko::select(['id', 'nama_gudang_toko'])
                ->where('id', '!=', 1)
                ->where('kategori_bangunan', '=', 0)
                ->get();
            $kurir = Kurir::select(['id', 'nama_kurir'])->get();

            return response()->json([
                'status' => true,
                'message' => 'Data Barang, Jenis Penerimaan, dan Asal Barang',
                'data' => [
                    'barangs' => BarangCreateResource::collection($barangs),
                    'cabang' => CabangCreateResource::collection($cabang),
                    'kurir' => KurirCreateResource::collection($kurir),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data untuk form pengiriman dari pusat ke cabang.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_cabang' => 'required|exists:gudang_dan_tokos,id',
                'id_barang' => 'required|exists:barangs,id',
                'jumlah_barang' => 'required|integer|min:1',
                'id_satuan_berat' => 'required|exists:satuan_berats,id',
                'id_kurir' => 'required|exists:kurirs,id',
                'berat_satuan_barang' => 'required|numeric|min:1',
            ]);

            $barang = DetailGudang::where('id_gudang', 1)   // gudang pusat
                ->where('id_barang', $request->id_barang)
                ->firstOrFail(['jumlah_stok']);

            if ($barang->jumlah_stok < $request->jumlah_barang) {
                return response()->json([
                    'status' => false,
                    'message' => 'Jumlah stok tidak mencukupi untuk dikirim.',
                ], 409);
            }

            $currentTime = now();

            $pusatKeCabang = array_merge($validated, [
                'kode' => CodeHelpers::generatePusatKeCabangCode($currentTime),
                'id_pusat' => 1, 
                'id_status' => 1,
                'tanggal' => $currentTime,
            ]); 

            DB::transaction(function () use ($pusatKeCabang) {
                PusatKeCabang::create($pusatKeCabang);
            }, 3);
    
            return response()->json([
                'status' => true,
                'message' => 'Berhasil mengirimkan barang dari Pusat Ke Cabang.',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Stok barang tidak ditemukan.',
                'error' => $e->getMessage()
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
                'message' => 'Gagal mengirimkan barang dari Pusat Ke Cabang.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $pusatKeCabang = PusatKeCabang::with(
            'pusat:id,nama_gudang_toko', 
                'cabang:id,nama_gudang_toko', 
                'barang:id,nama_barang',
                'kurir:id,nama_kurir', 
                'satuanBerat:id,nama_satuan_berat', 
                'status:id,nama_status'
            )->findOrFail($id, [
                'id', 'kode', 'id_barang',
                'id_pusat', 'id_cabang', 
                'id_satuan_berat', 'berat_satuan_barang', 
                'jumlah_barang', 'tanggal',
                'id_kurir', 'id_status',
            ]);
            
            $namaBarang = $pusatKeCabang->barang->nama_barang ?? 'Tidak diketahui';
            $namaCabang = $pusatKeCabang->cabang->nama_gudang_toko ?? 'Tidak diketahui';

            return response()->json([
                'status' => true,
                'message' => "Detail pengiriman barang '{$namaBarang}' ke cabang '{$namaCabang}'",
                'data' => new PusatKeCabangShowResource($pusatKeCabang),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data pengiriman tidak ditemukan.",
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil data pengiriman.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $pusatKeCabang = PusatKeCabang::findOrFail($id);

            // Assuming 'flag' is used for soft deletes (0 for deleted, 1 for active)
            if ($pusatKeCabang->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Pengiriman ke cabang '{$pusatKeCabang->cabang->nama_gudang_toko}' sudah dihapus sebelumnya.",
                ], 409); // Conflict
            }

            DB::transaction(function () use ($id, $pusatKeCabang) {
                $pusatKeCabang->update(['flag' => 0]);
            }, 3);
            
            return response()->json([
                'status' => true,
                'message' => "Berhasil menghapus pengiriman barang '{$pusatKeCabang->barang->nama_barang}' ke cabang '{$pusatKeCabang->cabang->nama_gudang_toko}'",
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data pengiriman tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Gagal menghapus data pengiriman.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'id_status' => 'required|exists:statuses,id',
            ]);

            $pusatKeCabang = PusatKeCabang::findOrFail($id);

            if ($pusatKeCabang->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Pengiriman ke cabang '{$pusatKeCabang->cabang->nama_gudang_toko}' sudah dihapus sebelumnya.",
                ], 409); // Conflict
            }

            $pusatKeCabang->update($validated);

            $namaCabang = $pusatKeCabang->cabang->nama_gudang_toko;
            $namaStatusBaru = Status::find($validated['id_status'])->nama_status;

            return response()->json([
                'status' => true,
                'message' => "Status pengiriman ke cabang '{$namaCabang}' telah diperbarui menjadi '{$namaStatusBaru}'",
                'data' => new PusatKeCabangIndexResource($pusatKeCabang),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data pengiriman tidak ditemukan.",
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
                'message' => "Terjadi kesalahan saat memperbarui status pengiriman.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}