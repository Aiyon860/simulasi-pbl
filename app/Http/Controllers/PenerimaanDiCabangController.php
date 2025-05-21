<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\SatuanBerat;
use Illuminate\Http\Request;
use App\Models\GudangDanToko;
use App\Models\JenisPenerimaan;
use App\Models\PenerimaanDiCabang;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StorePenerimaanDiCabangRequest;

class PenerimaanDiCabangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $penerimaanDiCabang = PenerimaanDiCabang::with('jenisPenerimaan', 'asalBarang', 'barang', 'satuanBerat')->get();

            return response()->json([
                'status' => true,
                'message' => 'Data Penerimaan Di Cabang',
                'data' => $penerimaanDiCabang,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data Penerimaan Di Cabang.',
                'error' => $e->getMessage(), // Hanya tampilkan detail error saat development
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $barangs = Barang::all()->where('flag', 1);
            $jenisPenerimaan = JenisPenerimaan::all();
            $asalBarang = GudangDanToko::all()->where('flag', 1);
            $satuanBerat = SatuanBerat::all();

            return response()->json([
                'status' => true,
                'message' => 'Data Barang, Jenis Penerimaan, dan Asal Barang',
                'data' => [
                    'barangs' => $barangs,
                    'jenisPenerimaan' => $jenisPenerimaan,
                    'asalBarang' => $asalBarang,
                    'satuanBerat' => $satuanBerat,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data Penerimaan Di Cabang.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_cabang' => 'required|exists:gudang_dan_tokos,id',
            'id_barang' => 'required|exists:barangs,id',
            'id_jenis_penerimaan' => 'required|exists:jenis_penerimaans,id',
            'id_asal_barang' => 'required|exists:gudang_dan_tokos,id',
            'id_satuan_berat' => 'required|exists:satuan_berats,id',
            'berat_satuan_barang' => 'required|integer|min:1',
            'jumlah_barang' => 'required|integer|min:1',
            'tanggal' => 'required|date',
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                PenerimaanDiCabang::create($validated);

                return response()->json([
                    'status' => true,
                    'message' => 'Data Penerimaan Di Cabang berhasil ditambahkan',
                ]);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                // 'message' => 'Gagal menambahkan Data Penerimaan Di Cabang. Silakan coba lagi.',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $penerimaanDiCabang = PenerimaanDiCabang::with('jenisPenerimaan', 'asalBarang', 'barang')->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => "Data Penerimaan Di Cabang dengan ID: {$id}",
                'data' => $penerimaanDiCabang,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Data Penerimaan Di Cabang dengan ID: {$id} tidak ditemukan.",
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $penerimaanDiCabang = PenerimaanDiCabang::findOrFail($id);

            if ($penerimaanDiCabang->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Data Penerimaan Di Cabang dengan ID: {$id} sudah dihapus sebelumnya.",
                ]);
            }

            return DB::transaction(function () use ($id, $penerimaanDiCabang) {
                $penerimaanDiCabang->update(['flag' => 0]);

                return response()->json([
                    'status' => true,
                    'message' => "Berhasil menghapus Data Penerimaan Di Cabang dengan ID: {$id}",
                ]);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Gagal menghapus Data Penerimaan Di Cabang dengan ID: {$id} {$th->getMessage()}",
            ], 500);
        }
    }
}
