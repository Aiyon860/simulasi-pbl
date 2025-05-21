<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\SatuanBerat;
use App\Models\DetailGudang;
use Illuminate\Http\Request;
use App\Models\GudangDanToko;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DetailGudangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $detailGudang = DetailGudang::with('barang', 'gudang', 'satuanBerat')->where('id_gudang', auth()->user()->gudang->id)->get();

            return response()->json([
                'status' => true,
                'message' => 'Data Barang Gudang',
                'data' => $detailGudang,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data barang gudang.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try{
            $barangs = Barang::all();
            $gudang = GudangDanToko::all();
            $satuanBerat = SatuanBerat::all();

            return response()->json([
                'status' => true,
                'message' => 'Form Tambah Barang Gudang',
                'data' => [
                    'barangs' => $barangs,
                    'gudang' => $gudang,
                    'satuanBerat' => $satuanBerat,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menyiapkan form tambah barang gudang.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_barang' => 'required|exists:barangs,id',
            'id_gudang' => 'required|exists:gudang_dan_tokos,id',
            'id_satuan_berat' => 'required|exists:satuan_berats,id',
            'jumlah_stok' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                DetailGudang::create($validated);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

            return response()->json([
                'status' => true,
                'message' => 'Data Barang Gudang berhasil ditambahkan',
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data Barang Gudang tidak ditemukan',
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menambahkan data barang gudang',
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
            $detailGudang = DetailGudang::with('barang', 'gudang', 'satuanBerat')->findOrFail($id);
            return response()->json([
                'status' => true,
                'message' => 'Detail Barang Gudang',
                'data' => $detailGudang,
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Barang Gudang dengan ID: {$id} tidak ditemukan",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil data barang gudang dengan ID: {$id}",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $detailGudang = DetailGudang::findOrFail($id);
            $barangs = Barang::all();
            $gudang = GudangDanToko::all();
            $satuanBerat = SatuanBerat::all();

            return response()->json([
                'status' => true,
                'message' => 'Form Edit Barang Gudang',
                'data' => [
                    'detailGudang' => $detailGudang,
                    'barangs' => $barangs,
                    'gudang' => $gudang,
                    'satuanBerat' => $satuanBerat,
                ],
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Barang Gudang dengan ID: {$id} tidak ditemukan",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat menyiapkan form edit barang gudang",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'id_barang' => 'required|exists:barangs,id',
            'id_gudang' => 'required|exists:gudang_dan_tokos,id',
            'jumlah_stok' => 'required|integer|min:1',
            'id_satuan_berat' => 'required|exists:satuan_berats,id',
            'stok_opname' => 'nullable|integer|min:0|max:1', // Ditambahkan nullable agar tidak selalu wajib diisi
        ]);

        try {
            $detailGudang = DetailGudang::findOrFail($id);
            DB::transaction(function () use ($validated, $detailGudang) {
                $detailGudang->update($validated);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

            return response()->json([
                'status' => true,
                'message' => "Data Barang Gudang dengan ID: {$id} berhasil diperbarui",
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Barang Gudang dengan ID: {$id} tidak ditemukan",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat memperbarui data barang gudang dengan ID: {$id}",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $barangGudang = DetailGudang::findOrFail($id);
            DB::transaction(function () use ($barangGudang) {
                $barangGudang->update(['flag' => 0]);
            });

            return response()->json([
                'status' => true,
                'message' => "Data Barang Gudang dengan ID: {$id} berhasil dihapus",
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Barang Gudang dengan ID: {$id} tidak ditemukan",
            ], 404);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat menghapus data barang gudang dengan ID: {$id}",
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
