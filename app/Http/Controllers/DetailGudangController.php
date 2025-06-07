<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\SatuanBerat;
use App\Models\DetailGudang;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use App\Models\GudangDanToko;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\BarangCreateResource;
use App\Http\Resources\GudangCreateResource;
use App\Http\Resources\DetailGudangIndexResource;
use App\Http\Resources\SatuanBeratCreateResource;
use App\Http\Resources\CabangKePusatIndexResource;
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
            ])->where('id_gudang', $request->user()->lokasi->id)
            ->where('flag', 1)
            ->orderBy('stok_opname', 'asc')
            ->get();

            $headings = [
                'ID',
                'Nama Barang',
                'Nama Gudang',
                'Jumlah Stok',
                'Stok Opname',
            ];

            return response()->json([
                'status' => true,
                'message' => 'Data Barang Gudang',
                'data' => [
                    'detailGudangs' => DetailGudangIndexResource::collection($detailGudang),
                    /** @var array<int, string> */
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
                    'barangs' => BarangCreateResource::collection($barangs),
                    'gudang' => GudangCreateResource::collection($gudang),
                    'satuanBerat' => SatuanBeratCreateResource::collection($satuanBerat),
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
            'jumlah_stok' => 'required|integer|min:0',
        ]);

        $exists = DetailGudang::where('id_barang', $validated['id_barang'])
                ->where('id_gudang', $validated['id_gudang'])
                ->exists();

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'Kombinasi barang dan gudang tersebut sudah terdaftar.',
            ], 409); // 409 Conflict
        }

        try {
            DB::transaction(function () use ($validated) {
                DetailGudang::create($validated);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

            return response()->json([
                'status' => true,
                'message' => 'Data Barang Gudang berhasil ditambahkan',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data Barang Gudang tidak ditemukan',
                'error' => $e->getMessage(),
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang anda masukkan tidak valid',
                'error' => $e->getMessage(),
            ], 422);
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
                'data' => new DetailGudangIndexResource($detailGudang),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Barang Gudang dengan ID: {$id} tidak ditemukan",
                'error' => $e->getMessage(),
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
                    'detailGudang' => new DetailGudangIndexResource($detailGudang),
                    'barangs' => BarangCreateResource::collection($barangs),
                    'gudang' => GudangCreateResource::collection($gudang),
                    'satuanBerat' => SatuanBeratCreateResource::collection($satuanBerat),
                ],
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Barang Gudang dengan ID: {$id} tidak ditemukan",
                'error' => $e->getMessage(),
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

            DB::transaction(function () use ($id, $validated, $detailGudang) {
                $detailGudang->update($validated);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

            return response()->json([
                'status' => true,
                'message' => "Data Barang Gudang dengan ID: {$id} berhasil diperbarui",
                'data' => new DetailGudangIndexResource($detailGudang),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Barang Gudang dengan ID: {$id} tidak ditemukan",
                'error' => $e->getMessage(),
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data yang anda masukkan tidak valid",
                'error' => $e->getMessage(),
            ], 422);
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
        //
    }

    public function deactivate(string $id)
    {
        try {
            $detailGudang = DetailGudang::findOrFail($id);

            if ($detailGudang->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Barang Gudang dengan ID: {$id} sudah dinonaktifkan",
                ]);
            }

            DB::transaction(function () use ($detailGudang) {
                $detailGudang->update(['flag' => 0]);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

            return response()->json([
                'status' => true,
                'message' => "Data Barang Gudang dengan ID: {$id} berhasil dinonaktifkan",
                'data' => new DetailGudangIndexResource($detailGudang),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Barang Gudang dengan ID: {$id} tidak ditemukan",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat menonaktifkan data barang gudang dengan ID: {$id}",
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
