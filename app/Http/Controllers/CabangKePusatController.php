<?php
namespace App\Http\Controllers;
use App\Models\Kurir;
use App\Models\Barang;
use App\Models\Status;
use App\Models\SatuanBerat;
use App\Models\DetailGudang;
use Illuminate\Http\Request;
use App\Models\CabangKePusat;
use App\Models\GudangDanToko;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CabangKePusatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $CabangKePusat = CabangKePusat::select(
                'id', 'kode', 'id_pusat', 
                'id_cabang', 'id_barang', 'id_satuan_berat', 
                'id_kurir', 'id_status', 'berat_satuan_barang', 
                'jumlah_barang', 'tanggal'
            )->with(
                'pusat:id,nama_gudang_toko,alamat,no_telepon',
                'cabang:id,nama_gudang_toko,alamat,no_telepon',
                'barang:id,nama_barang',
                'kurir:id,nama_kurir',
                'satuanBerat:id,nama_satuan_berat',
                'status:id,nama_status'
            )->where('flag', 1)
            ->get();

            return response()->json([
                'status' => true,
                'message' => 'Data Cabang Ke Pusat',
                'data' => $CabangKePusat,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data Cabang Ke Pusat.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $barangs = Barang::select('id', 'nama_barang')->get();
            $status = Status::select('id', 'nama_status')->get();
            $kurir = Kurir::select('id', 'nama_kurir')->get();
            $cabang = GudangDanToko::select('id', 'nama_gudang_toko')
                ->where('id', '!=', 1)
                ->where('kategori_bangunan', '=', 0)
                ->get();
            $satuanBerat = SatuanBerat::select('id', 'nama_satuan_berat')->get();

            return response()->json([
                'status' => true,
                'message' => 'Data Barang, Jenis Penerimaan, dan Asal Barang',
                'data' => [
                    'barangs' => $barangs,
                    'cabangs' => $cabang,
                    'satuanBerat' => $satuanBerat,
                    'status' => $status,
                    'kurir' => $kurir,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data untuk form.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'kode' => 'required|string',
                'id_cabang' => 'required|exists:gudang_dan_tokos,id',
                'id_barang' => 'required|exists:barangs,id',
                'id_satuan_berat' => 'required|exists:satuan_berats,id',
                'id_kurir' => 'required|exists:kurirs,id',
                'id_status' => 'required|exists:statuses,id',
                'berat_satuan_barang' => 'required|numeric|min:1',
                'jumlah_barang' => 'required|integer|min:1',
                'tanggal' => 'required|date',
            ]);

            return DB::transaction(function () use ($validated, $request) {
                $barang = DetailGudang::where('id_cabang', $request->id_cabang)
                    ->where('id_barang', $request->id_barang)
                    ->first('jumlah_stok');

                if (!$barang || $barang->jumlah_stok < $request->jumlah_barang) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Jumlah stok tidak mencukupi untuk diretur.',
                    ], 400);
                }

                $validated['id_pusat'] = 1;
                CabangKePusat::create($validated);

                return response()->json([
                    'status' => true,
                    'message' => 'Barang Berhasil Dikirim Dari Cabang Ke Pusat.',
                ], 201);
            }, 3);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan tidak valid.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengirimkan barang dari Cabang Ke Pusat.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $cabangKePusat = CabangKePusat::with(
                'pusat:id,nama_gudang_toko,alamat,no_telepon',
                'cabang:id,nama_gudang_toko,alamat,no_telepon',
                'barang:id,nama_barang',
                'kurir:id,nama_kurir',
                'satuanBerat:id,nama_satuan_berat',
                'status:id,nama_status'
            )->findOrFail($id, [
                'id', 'kode', 'id_pusat', 
                'id_cabang', 'id_barang', 'id_satuan_berat', 
                'id_kurir', 'id_status', 'berat_satuan_barang', 
                'jumlah_barang', 'tanggal'
            ]);

            return response()->json([
                'status' => true,
                'message' => "Data Cabang Ke Pusat dengan ID: {$id}",
                'data' => $cabangKePusat,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Cabang Ke Pusat dengan ID: {$id} tidak ditemukan.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil data dengan ID: {$id}.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $cabangKePusat = CabangKePusat::findOrFail($id);

            if ($cabangKePusat->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Data Cabang Ke Pusat dengan ID: {$id} sudah dihapus sebelumnya.",
                ], 409);
            }

            return DB::transaction(function () use ($id, $cabangKePusat) {
                $cabangKePusat->update(['flag' => 0]);

                return response()->json([
                    'status' => true,
                    'message' => "Berhasil menghapus Data Cabang Ke Pusat dengan ID: {$id}",
                ]);
            }, 3);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Cabang Ke Pusat dengan ID: {$id} tidak ditemukan.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Gagal menghapus Data Cabang Ke Pusat dengan ID: {$id}.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
