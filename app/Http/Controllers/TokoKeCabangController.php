<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TokoKeCabang;
use Illuminate\Support\Facades\DB;
use App\Models\Barang;
use App\Models\Kurir;
use App\Models\GudangDanToko;
use App\Models\SatuanBerat;
use App\Models\Status;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TokoKeCabangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $TokoKeCabang = TokoKeCabang::with('toko', 'cabang', 'barang', 'kurir', 'satuanBerat', 'status')->get();

            return response()->json([
                'status' => true,
                'message' => 'Data Toko Ke Cabang berhasil diambil.',
                'data' => $TokoKeCabang,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data Toko Ke Cabang.',
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
            $barang = Barang::all();
            $satuanBerat = SatuanBerat::all();
            $kurir = Kurir::all();
            $toko = GudangDanToko::all();
            $cabang = $toko; // Assuming cabang is also from GudangDanToko
            $status = Status::all();

            return response()->json([
                'status' => true,
                'message' => 'Data pendukung untuk form Toko Ke Cabang.',
                'data' => [
                    'barang' => $barang,
                    'satuanBerat' => $satuanBerat,
                    'kurir' => $kurir,
                    'toko' => $toko,
                    'cabang' => $cabang,
                    'status' => $status,
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'kode' => 'required|string|unique:toko_ke_cabangs,kode',
                'id_cabang' => 'required|exists:gudang_dan_tokos,id',
                'id_toko' => 'required|exists:gudang_dan_tokos,id',
                'id_barang' => 'required|exists:barangs,id',
                'id_satuan_berat' => 'required|exists:satuan_berats,id',
                'id_kurir' => 'nullable|exists:kurirs,id',
                'id_status' => 'required|exists:statuses,id',
                'berat_satuan_barang' => 'required|numeric|min:0',
                'jumlah_barang' => 'required|integer|min:1',
                'tanggal' => 'required|date',
            ]);

            return DB::transaction(function () use ($validated) {
                $tokoKeCabang = TokoKeCabang::create($validated);

                return response()->json([
                    'status' => true,
                    'message' => 'Pengiriman berhasil dikirim dari Toko ke Cabang.',
                    'data' => $tokoKeCabang,
                ], 201); // 201 Created
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock
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

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $tokoKeCabang = TokoKeCabang::with('toko', 'cabang', 'barang', 'kurir', 'satuanBerat', 'status')->findOrFail($id);
            return response()->json([
                'status' => true,
                'message' => "Detail Data Toko Ke Cabang dengan ID: {$id}.",
                'data' => $tokoKeCabang,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Toko Ke Cabang dengan ID: {$id} tidak ditemukan.",
            ], 404); // Not Found
        } catch (\Exception $e) { // Catching a general Exception for other errors
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil detail data Toko Ke Cabang dengan ID: {$id}.",
                'error' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // This method is intentionally left empty as per your request.
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // This method is intentionally left empty as per your request.
    }

    /**
     * Remove the specified resource from storage (soft delete using 'flag').
     */
    public function destroy(string $id)
    {
        try {
            $tokoKeCabang = TokoKeCabang::findOrFail($id);

            // Check if the item is already "deleted" (flag == 0)
            if ($tokoKeCabang->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Data Toko Ke Cabang dengan ID: {$id} sudah dihapus.",
                ], 409); // Conflict status code for already deleted
            }

            return DB::transaction(function () use ($tokoKeCabang, $id) {
                $tokoKeCabang->update(['flag' => 0]);

                return response()->json([
                    'status' => true,
                    'message' => "Data Toko Ke Cabang dengan ID: {$id} berhasil dihapus (dinonaktifkan).",
                ]);
            }, 3);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Toko Ke Cabang dengan ID: {$id} tidak ditemukan.",
            ], 404); // Not Found
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Gagal menghapus Data Toko Ke Cabang dengan ID: {$id}.",
                'error' => $th->getMessage(),
            ], 500); // Internal Server Error
        }
    }
}
