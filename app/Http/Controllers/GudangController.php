<?php

namespace App\Http\Controllers;

use App\Http\Resources\GudangIndexResource;
use Illuminate\Http\Request;
use App\Models\GudangDanToko;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GudangController extends Controller
{
    public function index()
    {
        try {
            $GudangDanToko = GudangDanToko::where('kategori_bangunan', 0)
                ->orderBy('id')
                ->get([
                    'id', 
                    'nama_gudang_toko', 
                    'alamat', 
                    'no_telepon', 
                    'flag'
                ]);

            $headings = $GudangDanToko->isEmpty() ? [] : array_keys($GudangDanToko->first()->getAttributes());
            $headings = array_map(function ($heading) {
                return str_replace('_', ' ', ucfirst($heading));
            }, $headings);

            return response()->json([
                'status' => true,
                'message' => 'Data Gudang',
                'data' => [
                    'gudangs' => GudangIndexResource::collection($GudangDanToko),
                    /** @var array<int, string> */
                    'headings' => $headings,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data gudang.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function create()
    {
        try {
            return response()->json([
                'status' => true,
                'message' => 'Form Tambah Gudang',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menyiapkan form tambah gudang.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $gudang = GudangDanToko::all();
        try {
            $validated = $request->validate([
                'nama_gudang_toko' => 'required|string|max:255',
                'alamat' => 'nullable|string',
                'no_telepon' => 'nullable|string|max:20',
            ]);

            DB::transaction(function () use ($validated) {
                $gudang = GudangDanToko::create(array_merge($validated, ['kategori_bangunan' => 0]));

            }, 3);
            return response()->json([
                'status' => true,
                'message' => "Gudang {$gudang->nama_gudang_toko} berhasil ditambahkan.",
                'data' => $gudang,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan tidak valid.',
                'errors' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menyimpan gudang.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $gudang = GudangDanToko::findOrFail($id, [
                'id',
                'nama_gudang_toko',
                'alamat',
                'no_telepon',
                'flag'
            ]);

            return response()->json([
                'status' => true,
                'message' => "Detail Data Gudang dengan ID: {$id}",
                'data' => GudangIndexResource::collection($gudang),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Gudang dengan ID: {$id} tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil detail data Gudang dengan ID: {$id}.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit(string $id)
    {
        try {
            $gudang = GudangDanToko::findOrFail($id, [
                'id',
                'nama_gudang_toko',
                'alamat',
                'no_telepon'
            ]);

            return response()->json([
                'status' => true,
                'message' => "Data untuk Form Edit Gudang",
                'data' => GudangIndexResource::collection($gudang),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Gudang dengan ID: {$id} tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil data untuk form edit Gudang.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            
            $validated = $request->validate([
                'nama_gudang_toko' => 'required|string|max:255',
                'alamat' => 'nullable|string',
                'no_telepon' => 'nullable|string|max:20',
            ]);
            
            return DB::transaction(function () use ($id, $validated) {
                $gudang = GudangDanToko::findOrFail($id);
                $gudang->update($validated);

                return response()->json([
                    'status' => true,
                    'message' => "Gudang {$gudang->nama_gudang_toko} berhasil diperbarui.",
                    'data' => GudangIndexResource::collection($gudang)
                ]);
            }, 3);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan tidak valid.',
                'errors' => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Gudang dengan ID: {$id} tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat memperbarui data Gudang dengan ID: {$id}.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deactivate(string $id)
    {
        try {
            $gudang = GudangDanToko::findOrFail($id);

            if ($gudang->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Gudang {$gudang->nama_gudang_toko} sudah dinonaktifkan.",
                ], 400);
            }

            return DB::transaction(function () use ($gudang) {
                $gudang->update(['flag' => 0]);

                return response()->json([
                    'status' => true,
                    'message' => "Gudang {$gudang->nama_gudang_toko} berhasil dinonaktifkan.",
                    'data' => GudangIndexResource::collection($gudang),
                ]);
            }, 3);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Gudang dengan ID: {$id} tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat menonaktifkan Gudang dengan ID: {$id}.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function activate(string $id)
    {
        try {
            $gudang = GudangDanToko::findOrFail($id);

            if ($gudang->flag == 1) {
                return response()->json([
                    'status' => false,
                    'message' => "Gudang {$gudang->nama_gudang_toko} sudah diaktifkan.",
                ], 400);
            }

            return DB::transaction(function () use ($gudang) {
                $gudang->update(['flag' => 1]);

                return response()->json([
                    'status' => true,
                    'message' => "Gudang {$gudang->nama_gudang_toko} berhasil diaktifkan.",
                    'data' => GudangIndexResource::collection($gudang),
                ], 201);
            }, 3);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Gudang dengan ID: {$id} tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengaktifkan Gudang dengan ID: {$id}.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
