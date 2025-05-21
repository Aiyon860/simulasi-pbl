<?php

namespace App\Http\Controllers;

use App\Models\GudangDanToko;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TokoController extends Controller
{
    /**
     * Display a listing of the toko.
     */
    public function index()
    {
        try {
            $tokos = GudangDanToko::where('kategori_bangunan', 2)
                ->orderBy('id')
                ->paginate(10);

            return response()->json([
                'status' => true,
                'message' => 'Data Toko',
                'data' => $tokos,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data toko.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for creating a new toko.
     */
    public function create()
    {
        try {
            return response()->json([
                'status' => true,
                'message' => 'Form Tambah Toko',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menyiapkan form tambah toko.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created toko in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama_gudang_toko' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'alamat' => 'nullable|string',
                'no_telepon' => 'nullable|string|max:20',
            ]);

            $toko = GudangDanToko::create(array_merge($validated, ['kategori_bangunan' => 2]));

            return response()->json([
                'status' => true,
                'message' => "Toko {$toko->nama_gudang_toko} berhasil ditambahkan!",
                'data' => $toko,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan tidak valid.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menyimpan toko.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified toko.
     */
    public function show(string $id)
    {
        try {
            $toko = GudangDanToko::where('kategori_bangunan', 2)->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => "Detail Data Toko dengan ID: {$id}",
                'data' => $toko,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Toko dengan ID: {$id} tidak ditemukan.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil detail data Toko dengan ID: {$id}.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified toko.
     */
    public function edit(string $id)
    {
        try {
            $toko = GudangDanToko::where('kategori_bangunan', 2)->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => 'Data untuk Form Edit Toko',
                'data' => $toko,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Toko dengan ID: {$id} tidak ditemukan.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data untuk form edit Toko.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified toko in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $toko = GudangDanToko::where('kategori_bangunan', 2)->findOrFail($id);

            $validated = $request->validate([
                'nama_gudang_toko' => 'required|string|max:255',
                'alamat' => 'nullable|string',
                'no_telepon' => 'nullable|string|max:20',
            ]);

            $toko->update($validated);

            return response()->json([
                'status' => true,
                'message' => "Toko {$toko->nama_gudang_toko} berhasil diperbarui!",
                'data' => $toko,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan tidak valid.',
                'errors' => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Toko dengan ID: {$id} tidak ditemukan.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat memperbarui toko dengan ID {$id}.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Deactivate the specified toko from storage.
     */
    public function deactivate(string $id)
    {
        try {
            $toko = GudangDanToko::where('kategori_bangunan', 2)->findOrFail($id);

            $toko->update(['flag' => 0]);

            return response()->json([
                'status' => true,
                'message' => "Toko {$toko->nama_gudang_toko} berhasil dinonaktifkan!",
                'data' => $toko,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Toko dengan ID: {$id} tidak ditemukan.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat menonaktifkan toko dengan ID {$id}.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Activate the specified toko from storage.
     */
    public function activate(string $id)
    {
        try {
            $toko = GudangDanToko::where('kategori_bangunan', 2)->findOrFail($id);

            $toko->update(['flag' => 1]);

            return response()->json([
                'status' => true,
                'message' => "Toko {$toko->nama_gudang_toko} berhasil diaktifkan!",
                'data' => $toko,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Toko dengan ID: {$id} tidak ditemukan.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengaktifkan toko dengan ID {$id}.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
