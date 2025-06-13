<?php
namespace App\Http\Controllers;
use App\Models\Barang;
use Illuminate\Http\Request;
use App\Models\KategoriBarang;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\BarangEditResource;
use App\Http\Resources\BarangIndexResource;
use App\Http\Resources\BarangStoreResource;
use App\Http\Resources\KategoriBarangResource;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BarangController extends Controller
{
    public function index()
    {
        try {
            $barangs = Barang::with(['kategori:id,nama_kategori_barang'])
                ->where('flag', 1)
                ->orderBy('nama_barang')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Data Semua Barang',
                'data' => [
                    'barangs' => BarangIndexResource::collection($barangs),

                    /** @var array<int, 'ID' | 'Nama Barang' | 'Kategori Barang' | 'Status'> */
                    'headings' => [
                        'ID',
                        'Nama Barang',
                        'Kategori Barang',
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data Semua Barang.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function create()
    {
        try {
            $categories = KategoriBarang::select(['id', 'nama_kategori_barang'])
                ->where('flag', 1)
                ->orderBy('nama_kategori_barang')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Data Kategori Barang untuk Form Tambah Barang',
                'data' => [
                    'categories' => KategoriBarangResource::collection($categories),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data kategori barang untuk form tambah Barang.',
                'error' => $e->getMessage(), 
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nama_barang' => 'required|unique:barangs|string|max:255',
                'id_kategori_barang' => 'required|exists:kategori_barangs,id',
            ]);

            DB::transaction(function () use ($validated) {
                Barang::create($validated);
            }, 3);

            return response()->json([
                'status' => true,
                'message' => "Barang {$request->input('nama_barang')} berhasil ditambahkan!",
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data nama barang maupun kategori barang yang diberikan tidak valid.',
                'error' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data barang.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $barang = Barang::with('kategori:id,nama_kategori_barang')
                ->findOrFail($id, [
                    'id', 'nama_barang', 'id_kategori_barang'
            ]);

            return response()->json([
                'status' => true,
                'message' => "Detail Data Barang {$barang->nama_barang}",
                'data' => new BarangIndexResource($barang),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Detail Barang yang dicari tidak ditemukan",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil data detail data Barang",
                'error' => $e->getMessage(), // Hanya tampilkan detail error saat development
            ], 500);
        }
    }

    public function edit(string $id)
    {
        try {
            $barang = Barang::with('kategori:id,nama_kategori_barang')
                ->findOrFail($id, [
                    'id', 'nama_barang', 'id_kategori_barang'
            ]);

            $categories = KategoriBarang::select('id', 'nama_kategori_barang')
                ->where('flag', 1)
                ->orderBy('nama_kategori_barang')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Data Detail Barang dan Kategori Barang untuk Form Edit Barang',
                'data' => [
                    'barang' => new BarangEditResource($barang),
                    'categories' => KategoriBarangResource::collection($categories),
                ],
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Detail Barang yang dicari tidak ditemukan",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data detail barang dan kategori barang untuk form edit Barang.',
                'error' => $e->getMessage(),
            ], 500); 
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $barang = Barang::findOrFail($id);

            $rules = [
                'nama_barang' => 'required|string|max:255',
                'id_kategori_barang' => 'nullable|exists:kategori_barangs,id',
            ];

            if ($request->input('nama_barang') !== $barang->nama_barang) {
                $rules['nama_barang'][] = 'unique:barangs';
            }

            $validated = $request->validate($rules);

            DB::transaction(function () use ($validated, $barang) {
                $barang->update($validated);
            }, 3);

            return response()->json([
                'status' => true,
                'message' => "Barang {$barang->nama_barang} berhasil diperbarui!",
                'data' => new BarangIndexResource($barang),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data nama barang dan kategori barang yang diberikan tidak valid.',
                'error' => $e->getMessage(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Barang yang dicari tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat memperbarui data barang.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deactivate(string $id)
    {
        try {
            $barang = Barang::findOrFail($id);

            if ($barang->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Barang {$barang->nama_barang} sudah dinonaktifkan sebelumnya!",
                ], 409);
            }

            DB::transaction(function () use ($barang) {
                $barang->update(['flag' => 0]);    
            }, 3);

            return response()->json([
                'status' => true,
                'message' => "Barang {$barang->nama_barang} berhasil dinonaktifkan!",
                'data' => new BarangIndexResource($barang),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Barang yang dicari tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat menonaktifkan data barang.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function activate(string $id)
    {
        try {
            $barang = Barang::findOrFail($id);

            if ($barang->flag == 1) {
                return response()->json([
                    'status' => false,
                    'message' => "Barang {$barang->nama_barang} sudah diaktifkan sebelumnya!",
                ], 409);
            }

            DB::transaction(function () use ($barang) {
                $barang->update(['flag' => 1]);
            }, 3);

            return response()->json([
                'status' => true,
                'message' => "Barang {$barang->nama_barang} berhasil diaktifkan!",
                'data' => new BarangIndexResource($barang),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Barang yang dicari tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengaktifkan data barang",
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}