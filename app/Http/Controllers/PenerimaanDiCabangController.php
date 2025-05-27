<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\SatuanBerat;
use Illuminate\Http\Request;
use App\Models\GudangDanToko;
use App\Models\JenisPenerimaan;
use App\Models\PenerimaanDiCabang;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\BarangIndexResource;
use App\Http\Resources\BarangCreateResource;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\AsalBarangCreateResource;
use App\Http\Resources\SatuanBeratCreateResource;
use App\Http\Resources\JenisPenerimaanCreateResource;
use App\Http\Resources\PenerimaanDiCabangIndexResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PenerimaanDiCabangController extends Controller
{
    public function index()
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

            $headings = $penerimaanDiCabang->isEmpty() ? [] : array_keys($penerimaanDiCabang->first()->getAttributes());
            $headings = array_map(function ($heading) {
                return str_replace('_', ' ', ucfirst($heading));
            }, $headings);

            return response()->json([
                'status' => true,
                'message' => 'Data Penerimaan Di Cabang',
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
            $asalBarang = GudangDanToko::select(['id', 'nama_gudang_toko'])
                ->where(function ($query) {
                    $query->where('id', '=', 1)
                        ->orWhere('kategori_bangunan', '=', 2);
                })
                ->where('flag', '=', 1)
                ->get();
            $satuanBerat = SatuanBerat::select(['id', 'nama_satuan_berat'])->get();

            return response()->json([
                'status' => true,
                'message' => 'Data untuk Form Tambah Penerimaan Di Cabang',
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
            // Jika Anda menggunakan StorePenerimaanDiCabangRequest, Anda bisa langsung menggunakan $request->validated();
            // Jika tidak, validasi manual seperti di bawah ini:
            $validated = $request->validate([
                'id_cabang' => 'required|exists:gudang_dan_tokos,id',
                'id_barang' => 'required|exists:barangs,id',
                'id_jenis_penerimaan' => 'required|exists:jenis_penerimaans,id',
                'id_asal_barang' => 'required|exists:gudang_dan_tokos,id',
                'id_satuan_berat' => 'required|exists:satuan_berats,id',
                'berat_satuan_barang' => 'required|numeric|min:1', // Mengubah ke numeric untuk desimal
                'jumlah_barang' => 'required|integer|min:1',
                'tanggal' => 'required|date',
            ]);

            DB::transaction(function () use ($validated) {
                PenerimaanDiCabang::create($validated);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

            return response()->json([
                'status' => true,
                'message' => 'Data Penerimaan Di Cabang berhasil ditambahkan!',
            ], 201); // 201 Created
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan tidak valid.',
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
                'message' => "Detail Data Penerimaan Di Cabang dengan ID: {$id}",
                'data' => PenerimaanDiCabangIndexResource::make($penerimaanDiCabang),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Penerimaan Di Cabang dengan ID: {$id} tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404); // 404 Not Found
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil detail data Penerimaan Di Cabang dengan ID: {$id}.",
                'error' => $e->getMessage(),
            ], 500); // 500 Internal Server Error
        }
    }

    public function destroy(string $id)
    {
        try {
            $penerimaanDiCabang = PenerimaanDiCabang::findOrFail($id);

            // Opsional: Cek jika flag sudah 0, untuk menghindari penghapusan berulang
            if ($penerimaanDiCabang->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Data Penerimaan Di Cabang dengan ID: {$id} sudah tidak aktif.",
                ], 409); // 409 Conflict
            }

            DB::transaction(function () use ($penerimaanDiCabang) {
                $penerimaanDiCabang->update(['flag' => 0]); // Soft delete dengan mengubah flag
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

            return response()->json([
                'status' => true,
                'message' => "Data Penerimaan Di Cabang dengan ID: {$id} berhasil dinonaktifkan!",
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Penerimaan Di Cabang dengan ID: {$id} tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat menonaktifkan Data Penerimaan Di Cabang dengan ID: {$id}.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}