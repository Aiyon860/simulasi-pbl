<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KategoriBarang;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class KategoriBarangController extends Controller
{
    public function index()
    {
        try {
            $categories = KategoriBarang::select([
                'id', 'nama_kategori_barang', 'flag'
            ])->orderBy('id')
            ->get();

            $headings = $categories->isEmpty() ? [] : array_keys($categories->first()->getAttributes());
            $headings = array_map(function ($heading) {
                return str_replace('_', ' ', ucfirst($heading));
            }, $headings);

            return response()->json([
                'status' => true,
                'message' => 'Data Kategori Barang',
                'data' => [
                    'kategoriBarangs' => $categories,
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

            return DB::transaction(function () use ($validated) {
                $kategoriBarang = KategoriBarang::create($validated);

                return response()->json([
                    'status' => true,
                    'message' => "Data Kategori Barang {$kategoriBarang->nama_kategori_barang} berhasil ditambahkan",
                    'data' => $kategoriBarang,
                ]. 201);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => "Gagal menambahkan kategori barang. Silakan coba lagi.",
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Gagal menambahkan kategori barang. Silakan coba lagi. Error: {$th->getMessage()}",
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $category = KategoriBarang::findOrFail($id, [
                'id', 'nama_kategori_barang', 'flag'
            ]);

            $kategori = [
                'id' => $category->id,
                'nama_kategori_barang' => $category->nama_kategori_barang,
                'status' => $category->flag ? 'Aktif' : 'Nonaktif',
            ];

            return response()->json([
                'status' => true,
                'message' => "Data Kategori Barang {$id}",
                'data' => $kategori,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Kategori Barang dengan ID: {$id} tidak ditemukan.",
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil data kategori barang dengan ID: {$id}.",
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
                'message' => "Form Edit Kategori Barang {$id}",
                'data' => $category,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Kategori Barang dengan ID: {$id} tidak ditemukan.",
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat menyiapkan form edit kategori barang dengan ID: {$id}.",
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $category = KategoriBarang::findOrFail($id);

            $rules = [
                'nama_kategori_barang' => 'required|string|max:255',
            ];

            if ($request->input('nama_kategori_barang') !== $category->nama_kategori_barang) {
                $rules['nama_kategori_barang'][] = Rule::unique('kategori_barangs')->ignore($category->id);
            }

            $validated = $request->validate($rules);

            DB::transaction(function () use ($validated, $category) {
                $category->update($validated);

                return response()->json([
                    'status' => true,
                    'message' => "Data Kategori Barang {$category->nama_kategori_barang} berhasil diperbarui",
                    'data' => $category,
                ]);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => "Gagal memperbarui kategori barang. Silakan coba lagi.",
                'errors' => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Kategori Barang dengan ID: {$id} tidak ditemukan.",
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Gagal memperbarui kategori barang. Silakan coba lagi. Error: {$th->getMessage()}",
            ], 500);
        }
    }

    public function deactivate(string $id)
    {
        try {
            $category = KategoriBarang::findOrFail($id);

            if($category->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Kategori barang dengan ID: {$id} sudah dinonaktifkan"
                ]);
            }

            return DB::transaction(function () use ($id, $category) {
                $category->update(['flag' => 0]);

                return response()->json([
                    'status' => true,
                    'message' => "Data Kategori barang dengan ID: {$id} berhasil dinonaktifkan",
                    'data' => $category,
                ]);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Kategori barang dengan ID: {$id} tidak ditemukan"
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Gagal menonaktifkan Kategori barang dengan ID: {$id}"
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
                    'message' => "Kategori barang dengan ID: {$id} sudah diaktifkan"
                ]);
            }
            
            return DB::transaction(function () use ($id, $category) {
                $category->update(['flag' => 1]);

                return response()->json([
                    'status' => true,
                    'message' => "Kategori barang dengan ID: {$id} berhasil diaktifkan",
                    'data' => $category,
                ]);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Kategori barang dengan ID: {$id} tidak ditemukan"
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Gagal mengaktifkan Kategori barang dengan ID: {$id}"
            ], 500);
        }
    }
}
