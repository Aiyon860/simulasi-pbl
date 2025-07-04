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
                    'flag',
                ]);

            $headings = [
                "NO",
                "Nama Gudang",
                "Alamat",
                "No telepon",
                "Status"
            ];

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

            $gudang = array_merge($validated, ['kategori_bangunan' => 0]);

            DB::transaction(function () use ($gudang) {
                GudangDanToko::create($gudang);
            }, 3);

            return response()->json([
                'status' => true,
                'message' => "Berhasil menambahkan data gudang.",
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan tidak valid.',
                'errors' => $e->errors()
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
                'message' => "Detail Data {$gudang->nama_gudang_toko}",
                'data' => new GudangIndexResource($gudang),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data gudang tidak ditemukan.",
                'error' => $e->getMessage(), 
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil detail data gudang.",
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
                'data' => new GudangIndexResource($gudang),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data gudang tidak ditemukan.",
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

            $gudang = GudangDanToko::findOrFail($id);

            DB::transaction(function () use ($gudang, $validated) {
                $gudang->update($validated);
            }, 3);

            return response()->json([
                'status' => true,
                'message' => "{$gudang->nama_gudang_toko} berhasil diperbarui.",
                'data' => new GudangIndexResource($gudang)
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
                'message' => "Data gudang tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat memperbarui data gudang.",
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
                    'message' => "{$gudang->nama_gudang_toko} sudah dinonaktifkan.",
                ], 409);
            }

            DB::transaction(function () use ($gudang) {
                $gudang->update(['flag' => 0]);
            }, 3);

            return response()->json([
                'status' => true,
                'message' => "{$gudang->nama_gudang_toko} berhasil dinonaktifkan.",
                'data' => new GudangIndexResource($gudang),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data gudang tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat menonaktifkan data gudang.",
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
                    'message' => " {$gudang->nama_gudang_toko} sudah diaktifkan.",
                ], 400);
            }

            DB::transaction(function () use ($gudang) {
                $gudang->update(['flag' => 1]);
            }, 3);

            return response()->json([
                'status' => true,
                'message' => "{$gudang->nama_gudang_toko} berhasil diaktifkan.",
                'data' => new GudangIndexResource($gudang),
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data gudang tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengaktifkan data gudang.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
