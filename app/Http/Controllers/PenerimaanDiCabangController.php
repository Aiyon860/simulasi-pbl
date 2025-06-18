<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\SatuanBerat;
use Illuminate\Http\Request;
use App\Models\GudangDanToko;
use App\Models\JenisPenerimaan;
use App\Models\PenerimaanDiCabang;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\BarangCreateResource;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\AsalBarangCreateResource;
use App\Http\Resources\SatuanBeratCreateResource;
use App\Http\Resources\JenisPenerimaanCreateResource;
use App\Http\Resources\PenerimaanDiCabangShowResource;
use App\Http\Resources\PenerimaanDiCabangIndexResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PenerimaanDiCabangController extends Controller
{
    public function index(Request $request)
    {
        try {
            $penerimaanDiCabang = PenerimaanDiCabang::select([
                'id', 'id_cabang', 'id_barang', 'id_jenis_penerimaan',
                'id_asal_barang', 'id_satuan_berat', 'berat_satuan_barang',
                'jumlah_barang', 'tanggal'
            ])->with([
                'jenisPenerimaan:id,nama_jenis_penerimaan',
                'asalBarang:id,nama_gudang_toko',
                'barang:id,nama_barang',
                'satuanBerat:id,nama_satuan_berat'
            ])
            ->where('flag', '=', 1)
            ->orderBy('tanggal', 'desc')
            ->get();

            $headings = [
                'ID',
                'Nama Barang',
                'Asal Barang',
                'Jumlah Barang',
                'Tanggal',
                'Jenis Penerimaan',
            ];

            return response()->json([
                'status' => true,
                'message' => "Data Penerimaan Di {$request->user()->lokasi->nama_gudang_toko}",
                'data' => [
                    'penerimaanDiCabangs' => PenerimaanDiCabangIndexResource::collection($penerimaanDiCabang),

                    /** @var array<int, string> */
                    'headings' => $headings,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data Penerimaan Di Cabang.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function create()
    {
        try {
            $barangs = Barang::select(['id', 'nama_barang'])
                ->where('flag', 1)
                ->get();
            $jenisPenerimaan = JenisPenerimaan::select(['id', 'nama_jenis_penerimaan'])->get();
            $asalBarang = GudangDanToko::select(['id', 'nama_gudang_toko', 'kategori_bangunan'])
                ->where(function ($query) {
                    $query->where('id', '=', 1)
                        ->orWhere('kategori_bangunan', '=', 2);
                })
                ->where('flag', '=', 1)
                ->get();
            $satuanBerat = SatuanBerat::select(['id', 'nama_satuan_berat'])->get();

            return response()->json([
                'status' => true,
                'message' => 'Data barang, jenis penerimaan, dan asal barang untuk Form Tambah Data Penerimaan Di Cabang',
                'data' => [
                    'barangs' => BarangCreateResource::collection($barangs),
                    'jenisPenerimaan' => JenisPenerimaanCreateResource::collection($jenisPenerimaan),
                    'asalBarang' => AsalBarangCreateResource::collection($asalBarang),
                    'satuanBerat' => SatuanBeratCreateResource::collection($satuanBerat),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data untuk form tambah Penerimaan Di Cabang.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_cabang' => 'required|exists:gudang_dan_tokos,id',
                'id_barang' => 'required|exists:barangs,id',
                'id_jenis_penerimaan' => 'required|exists:jenis_penerimaans,id',
                'id_asal_barang' => 'required|exists:gudang_dan_tokos,id',
                'id_satuan_berat' => 'required|exists:satuan_berats,id',
                'berat_satuan_barang' => 'required|numeric|min:1',
                'jumlah_barang' => 'required|integer|min:1',
            ]);

            $currentTime = now();

            $penerimaanDiCabang = array_merge($validated, [
                'tanggal' => $currentTime,
            ]);

            DB::transaction(function () use ($penerimaanDiCabang) {
                PenerimaanDiCabang::create($penerimaanDiCabang);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

            return response()->json([
                'status' => true,
                'message' => "Data Penerimaan Di {$request->user()->lokasi->nama_gudang_toko} berhasil ditambahkan!",
            ], 201); // 201 Created
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang dibutuhkan untuk laporan penerimaan di cabang yang diberikan tidak valid.',
                'error' => $e->getMessage(),
            ], 422); // 422 Unprocessable Entity
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data Penerimaan Di Cabang.',
                'error' => $e->getMessage(),
            ], 500); // 500 Internal Server Error
        }
    }

    public function show(string $id)
    {
        try {
            $penerimaanDiCabang = PenerimaanDiCabang::with([
                'jenisPenerimaan:id,nama_jenis_penerimaan',
                'asalBarang:id,nama_gudang_toko',
                'barang:id,nama_barang',
                'satuanBerat:id,nama_satuan_berat'
            ])->findOrFail($id, [
                'id', 'id_cabang', 'id_barang', 'id_jenis_penerimaan',
                'id_asal_barang', 'id_satuan_berat', 'berat_satuan_barang',
                'jumlah_barang', 'tanggal'
            ]);

            return response()->json([
                'status' => true,
                'message' => "Detail Data Penerimaan Di Cabang dengan asal barang dari {$penerimaanDiCabang->asalBarang->nama_gudang_toko}",
                'data' => new PenerimaanDiCabangShowResource($penerimaanDiCabang),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Penerimaan Di Cabang yang dicari tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404); // 404 Not Found
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil detail data Penerimaan Di Cabang.",
                'error' => $e->getMessage(),
            ], 500); // 500 Internal Server Error
        }
    }

    public function destroy(string $id)
    {
        try {
            $penerimaanDiCabang = PenerimaanDiCabang::with([
                'asalBarang:id,nama_gudang_toko',
            ])->findOrFail($id, [
                'id', 'id_cabang', 'id_barang', 'id_jenis_penerimaan',
                'id_asal_barang', 'id_satuan_berat', 'berat_satuan_barang',
                'jumlah_barang', 'tanggal'
            ]);

            if ($penerimaanDiCabang->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Data Penerimaan Di Cabang dengan asal barang dari {$penerimaanDiCabang->asalBarang->nama_gudang_toko} sudah tidak aktif.",
                ], 409); // 409 Conflict
            }

            DB::transaction(function () use ($penerimaanDiCabang) {
                $penerimaanDiCabang->update(['flag' => 0]); // Soft delete dengan mengubah flag
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

            return response()->json([
                'status' => true,
                'message' => "Data Penerimaan Di Cabang dengan ID: {$penerimaanDiCabang->asalBarang->nama_gudang_toko} berhasil dinonaktifkan!",
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Penerimaan Di Cabang yang dicari tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat menonaktifkan Data Penerimaan Di Cabang.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}