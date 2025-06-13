<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GudangDanToko;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\SupplierShowResource;
use App\Http\Resources\SupplierIndexResource;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SupplierController extends Controller
{
    public function index()
    {
        try {
            $suppliers = GudangDanToko::where('kategori_bangunan', 1)
                ->where('flag', '=', 1)
                ->orderBy('id')
                ->get([
                    'id', 'nama_gudang_toko', 'alamat', 'no_telepon'
                ]);

            $headings = $suppliers->isEmpty() ? [] : array_keys($suppliers->first()->getAttributes());
            $headings = array_map(function ($heading) {
                return str_replace('_', ' ', ucfirst($heading));
            }, $headings);

            return response()->json([
                'status' => true,
                'message' => 'Data Supplier',
                'data' => [
                    'suppliers' => SupplierIndexResource::collection($suppliers),

                    /** @var array<int, string> */
                    'headings' => $headings,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data supplier.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function create()
    {
        try {
            return response()->json([
                'status' => true,
                'message' => 'Form Tambah Supplier',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menyiapkan form tambah supplier.',
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

            DB::transaction(function () use ($validated) {
                GudangDanToko::create(array_merge($validated, ['kategori_bangunan' => 1]));
            }, 3);

            return response()->json([
                'status' => true,
                'message' => "Berhasil menambahkan data supplier!",
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan untuk form store supplier tidak valid.',
                'errors' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menyimpan supplier.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $supplier = GudangDanToko::findOrFail($id, [
                'id', 'nama_gudang_toko', 'alamat', 'no_telepon', 'flag'
            ]);

            return response()->json([
                'status' => true,
                'message' => "Detail Data Supplier {$supplier->nama_gudang_toko}",
                'data' => new SupplierShowResource($supplier),
            ]);
            
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Supplier yang dicari tidak ditemukan.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil detail data Supplier.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit(string $id)
    {
        try {
            $supplier = GudangDanToko::findOrFail($id, [
                'id', 'nama_gudang_toko', 'alamat', 'no_telepon'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Data untuk Form Edit Toko',
                'data' => new SupplierIndexResource($supplier),
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Supplier yang dicari tidak ditemukan.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data untuk form edit Supplier.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $supplier = GudangDanToko::findOrFail($id);

            $validated = $request->validate([
                'nama_gudang_toko' => 'required|string|max:255',
                'alamat' => 'nullable|string',
                'no_telepon' => 'nullable|string|max:20',   
            ]);

            DB::transaction(function () use ($supplier, $validated) {
                $supplier->update($validated);
            }, 3);

            return response()->json([
                'status' => true,
                'message' => "Supplier {$supplier->nama_gudang_toko} berhasil diperbarui!",
                'data' => new SupplierIndexResource($supplier),
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan untuk mengupdate data supplier tidak valid.',
                'errors' => $e->getMessage(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Supplier yang dicari tidak ditemukan.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat memperbarui supplier.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deactivate(string $id)
    {
        try {
            $supplier = GudangDanToko::findOrFail($id);

            if ($supplier->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Data Supplier {$supplier->nama_gudang_toko} sudah dinonaktifkan sebelumnya.",
                ], 409);
            }

            DB::transaction(function () use ($supplier) {
                $supplier->update(['flag' => 0]);
            }, 3);

            return response()->json([
                    'status' => true,
                    'message' => "Supplier {$supplier->nama_gudang_toko} berhasil dinonaktifkan!",
                    'data' => new SupplierIndexResource($supplier),
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Supplier {$supplier->nama_gudang_toko} tidak ditemukan.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat menonaktifkan supplier.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function activate(string $id)
    {
        try {
            $supplier = GudangDanToko::findOrFail($id);

            if ($supplier->flag == 1) {
                return response()->json([
                    'status' => false,
                    'message' => "Data Supplier {$supplier->nama_gudang_toko} sudah diaktifkan sebelumnya.",
                ], 409);
            }

            DB::transaction(function () use ($supplier) {
                $supplier->update(['flag' => 1]);
            }, 3);

            return response()->json([
                    'status' => true,
                    'message' => "Supplier {$supplier->nama_gudang_toko} berhasil diaktifkan!",
                    'data' => new SupplierIndexResource($supplier),
            ]);
            
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Supplier yang dicari tidak ditemukan.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengaktifkan supplier.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}