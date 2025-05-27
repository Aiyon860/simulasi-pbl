<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\SatuanBerat;
use App\Models\DetailGudang;
use Illuminate\Http\Request;
use App\Models\GudangDanToko;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DetailGudangController extends Controller
{
    public function index(Request $request)
    {
        try{
            $detailGudang = DetailGudang::select([
                'id', 'id_barang', 'id_gudang', 
                'id_satuan_berat', 'jumlah_stok', 
                'stok_opname', 'flag'
            ])
            ->with([
                'barang:id,nama_barang', 
                'gudang:id,nama_gudang_toko', 
                'satuanBerat:id,nama_satuan_berat'
            ])->where('id_gudang', $request->user()->gudang->id)
            ->orderBy('stok_opname', 'asc')
            ->get();

            $headings = $detailGudang->isEmpty() ? [] : array_keys($detailGudang->first()->getAttributes());
            $headings = array_map(function ($heading) {
                return str_replace('_', ' ', ucfirst($heading));
            }, $headings);

            return response()->json([
                'status' => true,
                'message' => 'Data Barang Gudang',
                'data' => [
                    'detailGudangs' => $detailGudang,
                    'headings' => $headings,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data barang gudang.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function create()
    {
        try{
            $barangs = Barang::select(['id', 'nama_barang'])->get();
            $gudang = GudangDanToko::select(['id', 'nama_gudang_toko'])
                ->where('kategori_bangunan', '=', 0)
                ->get();
            $satuanBerat = SatuanBerat::select(['id', 'nama_satuan_berat'])->get();

            return response()->json([
                'status' => true,
                'message' => 'Form Tambah Barang Gudang',
                'data' => [
                    'barangs' => $barangs,
                    'gudang' => $gudang,
                    'satuanBerat' => $satuanBerat,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menyiapkan form tambah barang gudang.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_barang' => 'required|exists:barangs,id',
            'id_gudang' => 'required|exists:gudang_dan_tokos,id',
            'id_satuan_berat' => 'required|exists:satuan_berats,id',
            'jumlah_stok' => 'required|integer|min:1',
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                $detailGudang = DetailGudang::create($validated);

                return response()->json([
                    'status' => true,
                    'message' => 'Data Barang Gudang berhasil ditambahkan',
                    'data' => $detailGudang,
                ], 201);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data Barang Gudang tidak ditemukan',
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menambahkan data barang gudang',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $detailGudang = DetailGudang::with([
                'barang:id,nama_barang', 
                'gudang:id,nama_gudang_toko', 
                'satuanBerat:id,nama_satuan_berat'
            ])->findOrFail($id, [
                'id', 'id_barang', 'id_gudang', 
                'id_satuan_berat', 'jumlah_stok', 
                'stok_opname', 'flag'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Detail Barang Gudang',
                'data' => $detailGudang,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Barang Gudang dengan ID: {$id} tidak ditemukan",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil data barang gudang dengan ID: {$id}",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit(string $id)
    {
        try {
            $detailGudang = DetailGudang::with([
                'barang:id,nama_barang', 
                'gudang:id,nama_gudang_toko', 
                'satuanBerat:id,nama_satuan_berat'
            ])->findOrFail($id, [
                'id', 'id_barang', 'id_gudang', 
                'id_satuan_berat', 'jumlah_stok', 
                'stok_opname', 'flag'
            ]);
            $barangs = Barang::select(['id', 'nama_barang'])->get();
            $gudang = GudangDanToko::select(['id', 'nama_gudang_toko'])
                ->where('kategori_bangunan', '=', 0)
                ->get();
            $satuanBerat = SatuanBerat::select(['id', 'nama_satuan_berat'])->get();

            return response()->json([
                'status' => true,
                'message' => 'Form Edit Barang Gudang',
                'data' => [
                    'detailGudang' => $detailGudang,
                    'barangs' => $barangs,
                    'gudang' => $gudang,
                    'satuanBerat' => $satuanBerat,
                ],
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Barang Gudang dengan ID: {$id} tidak ditemukan",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat menyiapkan form edit barang gudang",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'id_barang' => 'required|exists:barangs,id',
            'id_gudang' => 'required|exists:gudang_dan_tokos,id',
            'jumlah_stok' => 'required|integer|min:1',
            'id_satuan_berat' => 'required|exists:satuan_berats,id',
            'stok_opname' => 'nullable|integer|min:0|max:1', // Ditambahkan nullable agar tidak selalu wajib diisi
        ]);

        try {
            $detailGudang = DetailGudang::findOrFail($id);

            return DB::transaction(function () use ($id, $validated, $detailGudang) {
                $detailGudang = $detailGudang->update($validated);

                return response()->json([
                    'status' => true,
                    'message' => "Data Barang Gudang dengan ID: {$id} berhasil diperbarui",
                    'data' => $detailGudang
                ]);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Barang Gudang dengan ID: {$id} tidak ditemukan",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat memperbarui data barang gudang dengan ID: {$id}",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $barangGudang = DetailGudang::findOrFail($id);

            return DB::transaction(function () use ($id, $barangGudang) {
                $detailGudang = $barangGudang->update(['flag' => 0]);

                return response()->json([
                    'status' => true,
                    'message' => "Data Barang Gudang dengan ID: {$id} berhasil dihapus",
                    'data' => $detailGudang
                ]);
            });
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Barang Gudang dengan ID: {$id} tidak ditemukan",
            ], 404);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat menghapus data barang gudang dengan ID: {$id}",
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}