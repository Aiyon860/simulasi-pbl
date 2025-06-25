<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KategoriBarang;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\KategoriBarangShowResource;
use App\Http\Resources\KategoriBarangIndexResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class KategoriBarangController extends Controller
{
    public function index()
    {
        try {
            $categories = KategoriBarang::select([
                'id', 'nama_kategori_barang'
            ])->where('flag', '=', 1)
            ->orderBy('id')
            ->get();

            $headings = $categories->isEmpty() ? [] : array_keys($categories->first()->getAttributes());
            $headings = array_map(function ($heading) {
                return str_replace('_', ' ', ucfirst($heading));
            }, $headings);

            return response()->json([
                'status' => true,
                'message' => 'Data Kategori Barang',
                'data' => [
                    'kategoriBarangs' => KategoriBarangIndexResource::collection($categories),
                    
                    /** @var array<int, string> */
                    'headings' => $headings,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data kategori barang.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function create()
    {
        try {
            return response()->json([
                'status' => true,
                'message' => 'Form Tambah Kategori Barang',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menyiapkan form tambah kategori barang.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama_kategori_barang' => 'required|string|max:255|unique:kategori_barangs',
            ]);

            DB::transaction(function () use ($validated) {
                KategoriBarang::create($validated);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

            return response()->json([
                'status' => true,
                'message' => "Berhasil menambahkan Data Kategori Barang baru.",
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => "Kategori barang ini sudah ada.",
                'error' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Gagal menambahkan kategori barang. Silakan coba lagi.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $category = KategoriBarang::findOrFail($id, [
                'id', 'nama_kategori_barang', 'flag'
            ]);

            return response()->json([
                'status' => true,
                'message' => "Data Kategori Barang {$category->nama_kategori_barang}",
                'data' => new KategoriBarangShowResource($category),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Kategori Barang yang dicari tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil data kategori barang.",
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function edit(string $id)
    {
        try {
            $category = KategoriBarang::findOrFail($id, [
                'id', 'nama_kategori_barang'
            ]);

            return response()->json([
                'status' => true,
                'message' => "Form Edit Kategori Barang {$category->nama_kategori_barang}",
                'data' => new KategoriBarangIndexResource($category),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Kategori Barang yang dicari tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat menyiapkan form edit kategori barang.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $category = KategoriBarang::findOrFail($id);

            $rules = [
                'nama_kategori_barang' => ['required', 'string', 'max:255'],
            ];

            if ($request->input('nama_kategori_barang') !== $category->nama_kategori_barang) {
                $rules['nama_kategori_barang'][] = Rule::unique('kategori_barangs')->ignore($category->id);
            }

            $validated = $request->validate($rules);

            DB::transaction(function () use ($validated, $category) {
                $category->update($validated);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

            return response()->json([
                'status' => true,
                'message' => "Data Kategori Barang {$category->nama_kategori_barang} berhasil diperbarui",
                'data' => new KategoriBarangIndexResource($category),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => "Gagal memperbarui kategori barang. Silakan coba lagi.",
                'error' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Kategori Barang yang dicari tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Gagal memperbarui kategori barang. Silakan coba lagi.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deactivate(string $id)
    {
        try {
            $category = KategoriBarang::findOrFail($id);

            if ($category->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Kategori barang {$category->nama_kategori_barang} sudah dinonaktifkan sebelumnya."
                ]);
            }

            DB::transaction(function () use ($category) {
                $category->update(['flag' => 0]);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

            return response()->json([
                'status' => true,
                'message' => "Data Kategori barang {$category->nama_kategori_barang} berhasil dinonaktifkan",
                'data' => new KategoriBarangIndexResource($category),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Kategori barang yang dicari tidak ditemukan",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Gagal menonaktifkan data Kategori barang",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function activate(string $id)
    {
        try {
            $category = KategoriBarang::findOrFail($id);

            if($category->flag == 1) {
                return response()->json([
                    'status' => false,
                    'message' => "Kategori barang {$category->nama_kategori_barang} sudah diaktifkan"
                ]);
            }
            
            DB::transaction(function () use ($category) {
                $category->update(['flag' => 1]);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

            return response()->json([
                'status' => true,
                'message' => "Kategori barang {$category->nama_kategori_barang} berhasil diaktifkan",
                'data' => new KategoriBarangIndexResource($category),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Kategori barang yang dicari tidak ditemukan",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Gagal mengaktifkan data kategori barang"
            ], 500);
        }
    }
}
