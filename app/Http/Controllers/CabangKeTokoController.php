<?php
namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\CabangKeToko;
use App\Models\DetailGudang;
use App\Models\GudangDanToko;
use App\Models\Kurir;
use App\Models\SatuanBerat;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CabangKeTokoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $cabangKeToko = CabangKeToko::with('cabang', 'toko', 'barang', 'kurir', 'satuanBerat', 'status')->get();
            return response()->json([
                'status' => true,
                'message' => 'Data Cabang Ke Toko',
                'data' => $cabangKeToko,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data Cabang Ke Toko.',
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
            $barang = Barang::all()->where('flag', 1);
            $satuanBerat = SatuanBerat::all();
            $kurir = Kurir::all();
            $status = Status::where('id', 1)->get();
            $cabang = GudangDanToko::where('flag', 1)->get();
            $toko = $cabang;

            return response()->json([
                'status' => true,
                'message' => 'Data Barang Cabang ke Toko',
                'data' => [
                    'barang' => $barang,
                    'satuanBerat' => $satuanBerat,
                    'kurir' => $kurir,
                    'status' => $status,
                    'cabang' => $cabang,
                    'toko' => $toko,
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'kode' => 'required|string',
                'id_cabang' => 'required|exists:gudang_dan_tokos,id',
                'id_toko' => 'required|exists:gudang_dan_tokos,id',
                'id_barang' => 'required|exists:barangs,id',
                'id_satuan_berat' => 'required|exists:satuan_berats,id',
                'id_kurir' => 'required|exists:kurirs,id',
                'id_status' => 'required|exists:statuses,id',
                'berat_satuan_barang' => 'required|numeric|min:1',
                'jumlah_barang' => 'required|integer|min:1',
                'tanggal' => 'required|date',
            ]);

            return DB::transaction(function () use ($validated, $request) {
                $barang = DetailGudang::where('id_gudang', $request->id_cabang)
                    ->where('id_barang', $request->id_barang)
                    ->first();

                if (!$barang || $barang->jumlah_stok < $request->jumlah_barang) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Jumlah stok tidak mencukupi untuk dikirim.',
                    ], 400); // Bad Request
                }

                CabangKeToko::create($validated);

                return response()->json([
                    'status' => true,
                    'message' => 'Barang berhasil terkirim ke Toko',
                ], 201); // Created
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan tidak valid.',
                'errors' => $e->errors(),
            ], 422); // Unprocessable Entity
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengirimkan barang. Silakan coba lagi.',
                'error' => $th->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $CabangKeToko = CabangKeToko::with('cabang', 'toko', 'barang', 'kurir', 'satuanBerat', 'status')->findOrFail($id);
            return response()->json([
                'status' => true,
                'message' => "Data Cabang Ke Toko dengan ID: {$id}",
                'data' => $CabangKeToko,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil data Cabang Ke Toko dengan ID: {$id}.",
                'error' => $th->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $cabangKeToko = CabangKeToko::findOrFail($id);
            $barang = Barang::all();
            $satuanBerat = SatuanBerat::all();
            $kurir = Kurir::all();
            $status = Status::all();
            $cabang = GudangDanToko::where('flag', 1)->get();
            $toko = $cabang;
            
            return response()->json([
                'status' => true,
                'message' => 'Data untuk Form Edit Cabang ke Toko',
                'data' => [
                    'cabangKeToko' => $cabangKeToko,
                    'barang' => $barang,
                    'satuanBerat' => $satuanBerat,
                    'kurir' => $kurir,
                    'status' => $status,
                    'cabang' => $cabang,
                    'toko' => $toko,
                ],
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data untuk form edit Cabang ke Toko.',
                'error' => $th->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $CabangKeToko = CabangKeToko::findOrFail($id);
            $validated = $request->validate([
                'kode' => 'required|string',
                'id_cabang' => 'required|exists:gudang_dan_tokos,id',
                'id_toko' => 'required|exists:gudang_dan_tokos,id',
                'id_barang' => 'required|exists:barangs,id',
                'id_satuan_berat' => 'required|exists:satuan_berats,id',
                'id_kurir' => 'required|exists:kurirs,id',
                'id_status' => 'required|exists:statuses,id',
                'berat_satuan_barang' => 'required|numeric|min:1',
                'jumlah_barang' => 'required|integer|min:1',
                'tanggal' => 'required|date',
            ]);

            return DB::transaction(function () use ($validated, $CabangKeToko) {
                $CabangKeToko->update($validated);

                return response()->json([
                    'status' => true,
                    'message' => 'Data Cabang Ke Toko berhasil diperbarui',
                    'data' => $CabangKeToko,
                ]);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan tidak valid.',
                'errors' => $e->errors(),
            ], 422); // Unprocessable Entity
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Cabang Ke Toko dengan ID: {$id} tidak ditemukan.",
            ], 404); // Not Found
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memperbarui data Cabang Ke Toko. Silakan coba lagi.',
                'error' => $th->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Remove the specified resource from storage.
     */
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

            return DB::transaction(function () use ($id, $CabangKeToko) {
                $CabangKeToko->update(['flag' => 0]);

                return response()->json([
                    'status' => true,
                    'message' => "Berhasil menghapus Data Cabang Ke Toko dengan ID: {$id}",
                ]);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock
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
