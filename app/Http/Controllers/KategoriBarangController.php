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
            return view('categories.index', compact('categories'));
        } catch (\Exception $e) {
            return view('categories.index', ['message' => 'Terjadi kesalahan saat mengambil data Kategori Barang.', 'error' => $e->getMessage()]);
        }
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        try {
            return view('categories.create');
        } catch (\Exception $e) {
            return view('categories.index', ['message' => 'Terjadi kesalahan saat menyiapkan form tambah Kategori Barang.', 'error' => $e->getMessage()]);
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

            DB::transaction(function () use ($validated) {
                KategoriBarang::create($validated);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

            return redirect()->route('categories.index')->with('success', 'Kategori barang berhasil ditambahkan.');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e)->withInput();
        } catch (\Throwable $th) {
            return redirect()->route('categories.index')->with('error', 'Gagal menambahkan kategori barang. Silakan coba lagi. Error: ' . $th->getMessage());
        }
    }

    /**
     * Display the specified category.
     */
    public function show(string $id)
    {
        try {
            $category = KategoriBarang::findOrFail($id);
            return view('categories.show', compact('category'));
        } catch (ModelNotFoundException $e) {
            return view('categories.index', ['message' => "Data Kategori Barang dengan ID: {$id} tidak ditemukan."]);
        } catch (\Throwable $th) {
            return view('categories.index', ['message' => "Terjadi kesalahan saat mengambil detail kategori barang dengan ID: {$id}.", 'error' => $th->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(string $id)
    {
        try {
            $category = KategoriBarang::findOrFail($id);
            return view('categories.edit', compact('category'));
        } catch (ModelNotFoundException $e) {
            return view('categories.index', ['message' => "Data Kategori Barang dengan ID: {$id} tidak ditemukan."]);
        } catch (\Throwable $th) {
            return view('categories.index', ['message' => "Terjadi kesalahan saat mengambil data untuk form edit kategori barang dengan ID: {$id}.", 'error' => $th->getMessage()]);
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

            return redirect()->route('categories.index')->with('success', 'Kategori barang berhasil diperbarui.');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e)->withInput();
        } catch (ModelNotFoundException $e) {
            return redirect()->route('categories.index')->with('error', "Data Kategori Barang dengan ID: {$id} tidak ditemukan.");
        } catch (\Throwable $th) {
            return redirect()->route('categories.index')->with('error', 'Gagal memperbarui kategori barang. Silakan coba lagi. Error: ' . $th->getMessage());
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

            return redirect()->route('categories.index')->with('success', "Kategori barang {$category->nama_kategori_barang} berhasil dinonaktifkan!");
        } catch (ModelNotFoundException $e) {
            return redirect()->route('categories.index')->with('error', "Data Kategori Barang dengan ID: {$id} tidak ditemukan.");
        } catch (\Throwable $th) {
            return redirect()->route('categories.index')->with('error', "Gagal menonaktifkan kategori barang dengan ID: {$id}. Error: " . $th->getMessage());
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

            return redirect()->route('categories.index')->with('success', "Kategori barang {$category->nama_kategori_barang} berhasil diaktifkan!");
        } catch (ModelNotFoundException $e) {
            return redirect()->route('categories.index')->with('error', "Data Kategori Barang dengan ID: {$id} tidak ditemukan.");
        } catch (\Throwable $th) {
            return redirect()->route('categories.index')->with('error', "Gagal mengaktifkan kategori barang dengan ID: {$id}. Error: " . $th->getMessage());
        }
    }
}