<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GudangDanToko;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\TokoShowResource;
use App\Http\Resources\TokoIndexResource;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TokoController extends Controller
{
    public function index()
    {
        try {
            $tokos = GudangDanToko::where('kategori_bangunan', 2)
                ->where('flag', '=', 1)
                ->orderBy('id')
                ->get([
                    'id', 'nama_gudang_toko', 'alamat', 'no_telepon'
                ]);

            $headings = $tokos->isEmpty() ? [] : array_keys($tokos->first()->getAttributes());
            $headings = array_map(function ($heading) {
                return str_replace('_', ' ', ucfirst($heading));
            }, $headings);

            return response()->json([
                'status' => true,
                'message' => 'Data Toko',
                'data' => [
                    'tokos' => TokoIndexResource::collection($tokos),

                    /** @var array<int, string> */
                    'headings' => $headings,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data toko.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

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

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama_gudang_toko' => 'required|string|max:255',
                'alamat' => 'nullable|string',
                'no_telepon' => 'nullable|string|max:20',
            ]);

            $toko = array_merge($validated, ['kategori_bangunan' => 2]);

            DB::transaction(function () use ($toko) {
                GudangDanToko::create($toko);
            }, 3);

            return response()->json([
                'status' => true,
                'message' => "Berhasil menambahkan data toko!",
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan tidak valid.',
                'error' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menyimpan toko.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $toko = GudangDanToko::findOrFail($id, [
                'id', 'nama_gudang_toko', 'alamat', 'no_telepon', 'flag'
            ]);

            return response()->json([
                'status' => true,
                'message' => "Detail Data Toko dengan ID: {$id}",
                'data' => new TokoShowResource($toko),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Toko dengan ID: {$id} tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil detail data Toko dengan ID: {$id}.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit(string $id)
    {
        try {
            $toko = GudangDanToko::findOrFail($id, [
                'id', 'nama_gudang_toko', 'alamat', 'no_telepon'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Data untuk Form Edit Toko',
                'data' => new TokoIndexResource($toko),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Toko dengan ID: {$id} tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data untuk form edit Toko.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $toko = GudangDanToko::findOrFail($id);

            $validated = $request->validate([
                'nama_gudang_toko' => 'required|string|max:255',
                'alamat' => 'nullable|string',
                'no_telepon' => 'nullable|string|max:20',
            ]);

            DB::transaction(function () use ($toko, $validated) {
                $toko->update($validated);
            }, 3);

            return response()->json([
                'status' => true,
                'message' => "Toko {$toko->nama_gudang_toko} berhasil diperbarui!",
                'data' => new TokoIndexResource($toko),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan tidak valid.',
                'error' => $e->getMessage(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Toko dengan ID: {$id} tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat memperbarui toko dengan ID {$id}.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deactivate(string $id)
    {
        try {
            $toko = GudangDanToko::findOrFail($id);

            if ($toko->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Toko {$toko->nama_gudang_toko} sudah dinonaktifkan sebelumnya!",
                ], 409);
            }

            DB::transaction(function () use ($toko) {
                $toko->update(['flag' => 0]);
            }, 3);

            return response()->json([
                'status' => true,
                'message' => "Toko {$toko->nama_gudang_toko} berhasil dinonaktifkan!",
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Toko dengan ID: {$id} tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat menonaktifkan toko dengan ID {$id}.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function activate(string $id)
    {
        try {
            $toko = GudangDanToko::findOrFail($id);

            if ($toko->flag == 1) {
                return response()->json([
                    'status' => false,
                    'message' => "Toko {$toko->nama_gudang_toko} sudah diaktifkan sebelumnya!",
                ]);
            }

            DB::transaction(function () use ($toko) {
                $toko->update(['flag' => 1]);
            }, 3);

            return response()->json([
                'status' => true,
                'message' => "Toko {$toko->nama_gudang_toko} berhasil diaktifkan!",
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Toko dengan ID: {$id} tidak ditemukan.",
                'error' => $e->getMessage(),
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
