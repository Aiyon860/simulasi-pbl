<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\KategoriBarang;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Kategori\StoreKategoriRequest;
use App\Http\Requests\Kategori\UpdateKategoriRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class KategoriBarangController extends Controller
{
    /**
     * Display a listing of the category.
     */
    public function index()
    {
        try {
            $categories = KategoriBarang::orderBy('id')->paginate(10);
            return response()->json([
                'status' => true,
                'message' => 'Data Kategori Barang',
                'data' => $categories,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data kategori barang.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for creating a new category.
     */
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

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama_kategori_barang' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('kategori_barangs'),
                ],
            ]);

            $category = DB::transaction(function () use ($validated) {
                KategoriBarang::create($validated);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

            return response()->json([
                'status' => true,
                'message' => "Data Kategori Barang {$category->nama_kategori_barang} berhasil ditambahkan",
            ]. 201);
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

    /**
     * Display the specified category.
     */
    public function show(string $id)
    {
        try {
            $category = KategoriBarang::findOrFail($id);
            return response()->json([
                'status' => true,
                'message' => "Data Kategori Barang {$id}",
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
                'message' => "Terjadi kesalahan saat mengambil data kategori barang dengan ID: {$id}.",
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(string $id)
    {
        try {
            $category = KategoriBarang::findOrFail($id);
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

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $category = KategoriBarang::findOrFail($id);

            $rules = [
                'nama_kategori_barang' => [
                    'required',
                    'string',
                    'max:255',
                ],
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
            ]);
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

    /**
     * Deactivate the specified category from storage.
     */
    public function deactivate(string $id)
    {
        try {
            $category = KategoriBarang::findOrFail($id);

            DB::transaction(function () use ($category) {
                $category->update(['flag' => 0]);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

            return response()->json([
                'status' => true,
                'message' => "Data Kategori barang dengan ID: {$id} berhasil dinonaktifkan"
            ]);
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

    /**
     * Activate the specified category from storage.
     */
    public function activate(string $id)
    {
        try {
            $category = KategoriBarang::findOrFail($id);

            DB::transaction(function () use ($category) {
                $category->update(['flag' => 1]);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

            return response()->json([
                'status' => true,
                'message' => "Kategori barang dengan ID: {$id} berhasil diaktifkan"
            ]);
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
