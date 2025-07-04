<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\DetailGudang;
use Illuminate\Http\Request;
use App\Models\GudangDanToko;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\BarangCreateResource;
use App\Http\Resources\GudangCreateResource;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\DetailGudangEditResource;
use App\Http\Resources\DetailGudangShowResource;
use App\Http\Resources\DetailGudangIndexResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DetailGudangController extends Controller
{
    public function index(Request $request)
    {
        try{
            $data = DetailGudang::select([
                'id', 'id_barang', 'id_gudang',
                'jumlah_stok', 'stok_opname'
            ])
            ->with([
                'barang:id,nama_barang',
                'gudang:id,nama_gudang_toko',
            ]);

            if ($request->user()->lokasi->id != 1) {
                $data->where('id_gudang', $request->user()->lokasi->id);
            }

            $detailGudang = $data->orderBy('stok_opname', 'asc')
            ->get();

            $headings = [
                "NO",
                "Nama Barang",
                "Nama Gudang",
                "Jumlah Stok",
                "Stok Opname"
            ];

            $opname = $request->attributes->get('opname_status');

            return response()->json([
                'status' => true,
                'message' => 'Data Barang Gudang',
                'data' => [
                    'detailGudangs' => DetailGudangIndexResource::collection($detailGudang),
                    'status_opname' => $opname,                    

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
            $barangs = Barang::select(['id', 'nama_barang'])
                ->where('flag', '=', 1)
                ->get();
            $gudang = GudangDanToko::select(['id', 'nama_gudang_toko'])
                ->where('kategori_bangunan', '=', 0)
                ->whereHas('gudangOpname', function ($query) {
                    $query->where('stok_opname', 0);
                })
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Form Tambah Barang Gudang',
                'data' => [
                    'barangs' => BarangCreateResource::collection($barangs),
                    'gudang' => GudangCreateResource::collection($gudang),
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
        ]);

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
                'error' => $e->errors()
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
                'barang:id,nama_barang,id_satuan_berat',
                'barang.satuanBerat:id,nama_satuan_berat',
                'gudang:id,nama_gudang_toko',
            ])->findOrFail($id, [
                'id', 'id_barang', 
                'id_gudang', 'jumlah_stok',
                'stok_opname', 'flag'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Detail Barang Gudang',
                'data' => new DetailGudangShowResource($detailGudang),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Barang Gudang tidak ditemukan",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil data barang gudang",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit(string $id)
    {
        try {
            $detailGudang = DetailGudang::with([
                'barang:id,nama_barang,id_satuan_berat',
                'barang.satuanBerat:id,nama_satuan_berat',
                'gudang:id,nama_gudang_toko',
            ])->findOrFail($id, [
                'id', 'id_barang', 
                'id_gudang', 'jumlah_stok',
                'stok_opname', 'flag'
            ]);
            $barangs = Barang::select(['id', 'nama_barang', 'id_satuan_berat'])->get();
            $gudang = GudangDanToko::select(['id', 'nama_gudang_toko'])
                ->where('kategori_bangunan', '=', 0)
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Form Edit Barang Gudang',
                'data' => [
                    'detailGudang' => new DetailGudangEditResource($detailGudang),
                    'barangs' => BarangCreateResource::collection($barangs),
                    'gudang' => GudangCreateResource::collection($gudang),
                ],
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Barang Gudang tidak ditemukan",
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
            'stok_opname' => 'nullable|integer|min:0|max:1', // Ditambahkan nullable agar tidak selalu wajib diisi
        ]);

        try {
            $detailGudang = DetailGudang::with(['barang:id,nama_barang'])->findOrFail($id);

            DB::transaction(function () use ($id, $validated, $detailGudang) {
                $detailGudang->update($validated);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

            return response()->json([
                'status' => true,
                'message' => "Data Barang Gudang berhasil diperbarui",
                'data' => new DetailGudangIndexResource($detailGudang),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Barang Gudang tidak ditemukan",
                'error' => $e->getMessage(),
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data yang anda masukkan tidak valid",
                'error' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat memperbarui data barang gudang",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $barangGudang = DetailGudang::with(['barang:id,nama_barang'])->findOrFail($id);

            DB::transaction(function () use ($barangGudang) {
                $barangGudang->update(['flag' => 0]);
            });

            return response()->json([
                'status' => true,
                'message' => "Data Barang Gudang dengan nama barang: {$barangGudang->barang->nama_barang} berhasil dihapus",
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Barang Gudang tidak ditemukan",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat menghapus data barang gudang",
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
