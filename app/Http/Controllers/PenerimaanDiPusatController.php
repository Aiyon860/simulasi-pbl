<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Helpers\CodeHelpers;
use Illuminate\Http\Request;
use App\Models\GudangDanToko;
use App\Models\JenisPenerimaan;
use App\Models\PenerimaanDiPusat;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\BarangCreateResource;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\AsalBarangCreateResource;
use App\Http\Resources\JenisPenerimaanCreateResource;
use App\Http\Resources\PenerimaanDiPusatShowResource;
use App\Http\Resources\PenerimaanDiPusatIndexResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Resources\PenerimaanDiPusatBarangCreateResource;

class PenerimaanDiPusatController extends Controller
{
    public function index(Request $request)
    {
        try {
            $penerimaanDiPusat = PenerimaanDiPusat::select([
                'id',
                'id_barang',
                'id_jenis_penerimaan',
                'id_asal_barang',
                'jumlah_barang',
                'diterima',
                'tanggal',
                'id_verifikasi',
            ])->with([
                'jenisPenerimaan:id,nama_jenis_penerimaan',
                'asalBarang:id,nama_gudang_toko',
                'barang:id,nama_barang',
                'verifikasi:id,jenis_verifikasi',
            ])->where('flag', '=', 1)
            ->get();

            $headings = [
                'NO',
                'Nama Barang',
                'Asal Barang',
                'Jumlah Barang',
                'Tanggal',
                'Jenis Penerimaan',
                'Sudah Diterima',
                'Verifikasi',
            ];

            $opname = $request->attributes->get('opname_status');

            return response()->json([
                'success' => true,
                'message' => 'Data Penerimaan Di Pusat retrieved successfully',
                'data' => [
                    'penerimaanDiPusats' => PenerimaanDiPusatIndexResource::collection($penerimaanDiPusat),
                    'status_opname' => $opname,

                    /** @var array<int, string> */
                    'headings' => $headings,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data Penerimaan Di Pusat.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function create()
    {
        try{
            $barangs = Barang::select(['id', 'nama_barang', 'id_satuan_berat', 'berat_satuan_barang'])
                ->with('satuanBerat:id,nama_satuan_berat')
                ->where('flag', '=', 1)
                ->orderBy('id')
                ->get();
            $jenisPenerimaan = JenisPenerimaan::select(['id', 'nama_jenis_penerimaan'])->get();
            $asalBarang = GudangDanToko::select(['id', 'nama_gudang_toko', 'kategori_bangunan'])
                ->where('id', '!=', 1)
                ->whereIn('kategori_bangunan', [0, 1])
                ->where('flag', '=', 1)
                ->orderBy('id')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Data Barang, Jenis Penerimaan, dan Asal Barang',
                'data' => [
                    'barangs' => PenerimaanDiPusatBarangCreateResource::collection($barangs),
                    'jenisPenerimaan' => JenisPenerimaanCreateResource::collection($jenisPenerimaan),
                    'asalBarang' => AsalBarangCreateResource::collection($asalBarang),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil data untuk form tambah Penerimaan Di Pusat.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_barang' => 'required|exists:barangs,id',
                'id_jenis_penerimaan' => 'required|exists:jenis_penerimaans,id',
                'id_asal_barang' => 'required|exists:gudang_dan_tokos,id',
                'jumlah_barang' => 'required|integer|min:1',
                'id_laporan_pengiriman' => 'nullable|exists:supplier_ke_pusats,id',
                'id_laporan_retur' => 'nullable|exists:cabang_ke_pusats,id',
            ]);

            $barangGeneral = Barang::findOrFail($request->id_barang, [
                'id', 'id_satuan_berat', 'berat_satuan_barang'
            ]);

            $currentTime = now();

            $penerimaanDiPusat = array_merge($validated, [
                'kode' => CodeHelpers::generatePenerimaanDiPusatCode($currentTime),
                'diterima' => 1,
                'id_satuan_berat' => $barangGeneral->id_satuan_berat,
                'berat_satuan_barang' => $barangGeneral->berat_satuan_barang,
                'tanggal' => $currentTime,
            ]);

            DB::transaction(function () use ($penerimaanDiPusat) {
                PenerimaanDiPusat::create($penerimaanDiPusat);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

            return response()->json([
                'status' => true,
                'message' => 'Data Penerimaan Di Pusat berhasil ditambahkan',
            ]);
        } catch (ValidationException $th) {
            return response()->json([
                'status' => false,
                'message' => "Data yang dikirim tidak valid.",
                'error' => $th->errors()
            ], 422);
        }
    }

    public function show(string $id)
    {
        try {
            $penerimaanDiPusat = PenerimaanDiPusat::with(
                'jenisPenerimaan:id,nama_jenis_penerimaan',
                'asalBarang:id,nama_gudang_toko',
                'barang:id,nama_barang',
                'pusat:id,nama_gudang_toko',
                'satuanBerat:id,nama_satuan_berat',
                'laporanPengiriman:id,kode',
                'laporanRetur:id,kode',
                'verifikasi:id,jenis_verifikasi',
            )->findOrFail($id, [
                'id',
                'kode',
                'id_barang',
                'id_jenis_penerimaan',
                'id_asal_barang',
                'id_satuan_berat',
                'id_laporan_pengiriman',
                'id_laporan_retur',
                'berat_satuan_barang',
                'jumlah_barang',
                'diterima',
                'tanggal',
                'id_verifikasi'
            ]);

            return response()->json([
                'success' => true,
                'message' => "Data Penerimaan Di Pusat dari {$penerimaanDiPusat->asalBarang->nama_gudang_toko}",
                'data' => new PenerimaanDiPusatShowResource($penerimaanDiPusat)
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Data Penerimaan Di Pusat tidak ditemukan.",
                'error' => $th->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil data Penerimaan Di Pusat.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'id_verifikasi' => 'nullable|exists:verifikasi,id',
            ]);

            $penerimaanDiPusat = PenerimaanDiPusat::with(
                'jenisPenerimaan:id,nama_jenis_penerimaan',
                'asalBarang:id,nama_gudang_toko',
                'barang:id,nama_barang',
                'pusat:id,nama_gudang_toko',
                'satuanBerat:id,nama_satuan_berat',
                'laporanPengiriman:id,kode',
                'laporanRetur:id,kode',
                'verifikasi:id,jenis_verifikasi',
            )->findOrFail($id, [
                'id',
                'kode',
                'id_barang',
                'id_jenis_penerimaan',
                'id_asal_barang',
                'id_satuan_berat',
                'id_laporan_pengiriman',
                'id_laporan_retur',
                'berat_satuan_barang',
                'jumlah_barang',
                'diterima',
                'tanggal',
                'id_verifikasi',
            ]);

            $pesan = null;
            $updatedFields = [];
            if (isset($validated['id_verifikasi'])) {
                $updatedFields = $validated;

                $pesan = "Barang {$penerimaanDiPusat->barang->nama_barang} berhasil diverifikasi dengan kode laporan penerimaan di pusat: {$penerimaanDiPusat->kode}.";
            } else {
                $updatedFields = ['diterima' => 1];

                $pesan = "Barang {$penerimaanDiPusat->barang->nama_barang} berhasil diterima dengan kode laporan penerimaan di pusat: {$penerimaanDiPusat->kode}";
            }

            DB::transaction(function () use ($penerimaanDiPusat, $updatedFields) {
                $penerimaanDiPusat->update($updatedFields);
            }, 3);

            return response()->json([
                'status' => true,
                'message' => $pesan,
                'data' => new PenerimaanDiPusatShowResource($penerimaanDiPusat),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan untuk mengupdate data laporan penerimaan di pusat tidak valid.',
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data laporan penerimaan di pusat yang dicari tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat memperbarui data laporan penerimaan di pusat.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $penerimaanDiPusat = PenerimaanDiPusat::with(
                'jenisPenerimaan:id,nama_jenis_penerimaan',
                'asalBarang:id,nama_gudang_toko',
                'barang:id,nama_barang',
                'pusat:id,nama_gudang_toko',
                'satuanBerat:id,nama_satuan_berat',
                'laporanPengiriman:id,kode',
                'laporanRetur:id,kode',
            )->findOrFail($id, [
                'id',
                'kode',
                'id_barang',
                'id_jenis_penerimaan',
                'id_asal_barang',
                'id_satuan_berat',
                'id_laporan_pengiriman',
                'id_laporan_retur',
                'berat_satuan_barang',
                'jumlah_barang',
                'diterima',
                'tanggal',
                'flag',
            ]);

            if ($penerimaanDiPusat->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Data Penerimaan Di Pusat dari {$penerimaanDiPusat->asalBarang->nama_gudang_toko} sudah dihapus sebelumnya",
                ], 409);
            }

            DB::transaction(function () use ($id, $penerimaanDiPusat) {
                $penerimaanDiPusat->update(['flag' => 0]);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

            return response()->json([
                'status' => true,
                'message' => "Data Penerimaan Di Pusat dari {$penerimaanDiPusat->asalBarang->nama_gudang_toko} berhasil dihapus",
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Penerimaan Di Pusat tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat menonaktifkan Data Penerimaan Di Pusat.",
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
