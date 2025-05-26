<?php
namespace App\Http\Controllers;
use App\Models\Kurir;
use App\Models\Barang;
use App\Models\SatuanBerat;
use App\Models\DetailGudang;
use Illuminate\Http\Request;
use App\Models\GudangDanToko;
use App\Models\PusatKeCabang;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PusatKeCabangController extends Controller
{
    public function index()
    {
        try {
            $pusatKeCabang = PusatKeCabang::select([
                'id', 'kode', 'id_barang',
                'id_pusat', 'id_cabang', 
                'id_satuan_berat', 'berat_satuan_barang', 
                'jumlah_barang', 'tanggal',
                'id_kurir', 'id_status',
            ])->with([
                'pusat:id,nama_gudang_toko', 
                'cabang:id,nama_gudang_toko', 
                'barang:id,nama_barang',
                'kurir:id,nama_kurir', 
                'satuanBerat:id,nama_satuan_berat', 
                'status:id,nama_status'
            ])->where('flag', '=', 1)
            ->get();

            return response()->json([
                'status'=> true,
                'message'=> 'Data Penerimaan Di Cabang',
                'data'=> $pusatKeCabang,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data pengiriman ke cabang.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function create()
    {
        try {
            $barangs = Barang::select(['id', 'nama_barang'])->get();
            $cabang = GudangDanToko::select(['id', 'nama_gudang_toko'])
                ->where('id', '!=', 1)
                ->where('kategori_bangunan', '=', 0)
                ->get();
            $kurir = Kurir::select(['id', 'nama_kurir'])->get();
            $satuanBerat = SatuanBerat::select(['id', 'nama_satuan_berat'])->get();

            return response()->json([
                'status' => true,
                'message' => 'Data Barang, Jenis Penerimaan, dan Asal Barang',
                'data' => [
                    'barangs' => $barangs,
                    'cabang' =>$cabang,
                    'satuanBerat' => $satuanBerat,
                    'kurir' => $kurir,
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
                'kode' => 'required|string',
                'id_cabang' => 'required|exists:gudang_dan_tokos,id',
                'id_barang' => 'required|exists:barangs,id',
                'jumlah_barang' => 'required|integer|min:1',
                'tanggal' => 'required|date',
                'id_satuan_berat' => 'required|exists:satuan_berats,id',
                'id_kurir' => 'required|exists:kurirs,id',
                'berat_satuan_barang' => 'required|numeric|min:1',
            ]);

            return DB::transaction(function () use ($validated, $request) {
                $barang = DetailGudang::where('id_gudang', 1)   // gudang pusat
                    ->where('id_barang', $request->id_barang)
                    ->firstOrFail(['jumlah_stok']);

                if ($barang->jumlah_stok < $request->jumlah_barang) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Jumlah stok tidak mencukupi untuk dikirim.',
                    ]);
                }

                $pusatKeCabang = PusatKeCabang::create(array_merge(
                    $validated, 
                    ['id_pusat' => 1, 'id_status' => 1]
                )); 
        
                return response()->json([
                    'status' => true,
                    'message' => 'Berhasil mengirimkan barang dari Pusat Ke Cabang.',
                    'data' => $pusatKeCabang,
                ]);
            }, 3);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Stok barang tidak ditemukan.',
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan tidak valid.',
                'errors' => $e->errors(),
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

            return response()->json([
                'status' => true,
                'message' => "Detail Data Pengiriman dari Pusat Ke Cabang dengan ID: {$id}",
                'data' => $pusatKeCabang,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Pusat Ke Cabang dengan ID: {$id} tidak ditemukan.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil data dengan ID: {$id}",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        try {
            $pusatKeCabang = PusatKeCabang::findOrFail($id);

            // Assuming 'flag' is used for soft deletes (0 for deleted, 1 for active)
            if ($pusatKeCabang->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Data Pengiriman dari Pusat Ke Cabang dengan ID: {$id} sudah dihapus sebelumnya.",
                ], 409); // Conflict
            }

            return DB::transaction(function () use ($id, $pusatKeCabang) {
                $pusatKeCabang->update(['flag' => 0]);

                return response()->json([
                    'status' => true,
                    'message' => "Berhasil menghapus Data Pengiriman dari Pusat Ke Cabang dengan ID: {$id}",
                ]);
            }, 3);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Pengiriman dari Pusat Ke Cabang dengan ID: {$id} tidak ditemukan.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Gagal menghapus Data Pengiriman dari Pusat Ke Cabang dengan ID: {$id}.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}