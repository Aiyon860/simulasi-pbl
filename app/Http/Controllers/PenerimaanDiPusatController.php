<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\SatuanBerat;
use Illuminate\Http\Request;
use App\Models\GudangDanToko;
use App\Models\JenisPenerimaan;
use App\Models\PenerimaanDiPusat;
use Illuminate\Support\Facades\DB;
use Dotenv\Exception\ValidationException;
use App\Http\Resources\BarangCreateResource;
use App\Http\Resources\AsalBarangCreateResource;
use App\Http\Resources\SatuanBeratCreateResource;
use App\Http\Resources\JenisPenerimaanCreateResource;
use App\Http\Resources\PenerimaanDiPusatShowResource;
use App\Http\Resources\PenerimaanDiPusatIndexResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PenerimaanDiPusatController extends Controller
{
    public function index(Request $request)
    {
        try {
            $penerimaanDiPusat = PenerimaanDiPusat::select([
                'id', 'id_barang',
                'id_jenis_penerimaan', 'id_asal_barang',
                'id_satuan_berat', 'berat_satuan_barang',
                'jumlah_barang', 'tanggal'
            ])->with([
                'jenisPenerimaan:id,nama_jenis_penerimaan',
                'asalBarang:id,nama_gudang_toko',
                'pusat:id,nama_gudang_toko',
                'barang:id,nama_barang',
                'satuanBerat:id,nama_satuan_berat'
            ])->where('flag', '=', 1)
            ->get();

            $headings = [
                'ID',
                'Nama Barang',
                'Asal Barang',
                'Jumlah Barang',
                'Tanggal',
                'Jenis Penerimaan',
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
            $barangs = Barang::select(['id', 'nama_barang'])
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
            $satuanBerat = SatuanBerat::select(['id', 'nama_satuan_berat'])->get();

            return response()->json([
                'status' => true,
                'message' => 'Data Barang, Jenis Penerimaan, dan Asal Barang',
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
                'id_satuan_berat' => 'required|exists:satuan_berats,id',
                'berat_satuan_barang' => 'required|integer|min:1',
                'jumlah_barang' => 'required|integer|min:1',
            ]);

            $currentTime = now();

            $penerimaanDiPusat = array_merge($validated, [
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
                'error' => $th->getMessage(),
            ], 422);
        }
    }

    public function show(string $id)
    {
        try{
            $penerimaanDiPusat = PenerimaanDiPusat::with(
                'jenisPenerimaan:id,nama_jenis_penerimaan',
                'asalBarang:id,nama_gudang_toko',
                'barang:id,nama_barang',
                'pusat:id,nama_gudang_toko',
                'satuanBerat:id,nama_satuan_berat'
            )->findOrFail($id, [
                'id', 'id_barang',
                'id_jenis_penerimaan', 'id_asal_barang',
                'id_satuan_berat', 'berat_satuan_barang',
                'jumlah_barang', 'tanggal'
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
        }
    }

    public function destroy(string $id)
    {
        try {
            $penerimaanDiPusat = PenerimaanDiPusat::with(['asalBarang:id,nama_gudang_toko'])->findOrFail($id);

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
