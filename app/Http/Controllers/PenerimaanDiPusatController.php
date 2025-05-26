<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PenerimaanDiPusat;
use App\Models\JenisPenerimaan;
use App\Models\GudangDanToko;
use App\Models\SatuanBerat;
use App\Models\Barang;
use Illuminate\Support\Facades\DB;

class PenerimaanDiPusatController extends Controller
{
    public function index()
    {
        $penerimaanDiPusat = PenerimaanDiPusat::select([
            'id', 'id_barang',
            'id_jenis_penerimaan', 'id_asal_barang', 
            'id_satuan_berat', 'berat_satuan_barang', 
            'jumlah_barang', 'tanggal' 
        ])->with([
            'jenisPenerimaan:id,nama_jenis_penerimaan', 
            'asalBarang:id,nama_gudang_toko', 
            'barang:id,nama_barang', 
            'satuanBerat:id,nama_satuan_berat'
        ])->where('flag', '=', 1)
        ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data Penerimaan Di Pusat retrieved successfully',
            'data' => $penerimaanDiPusat
        ]);
    }

    public function create()
    {
        $barangs = Barang::select(['id', 'nama_barang'])
            ->where('flag', '=', 1)
            ->orderBy('id')
            ->get();
        $jenisPenerimaan = JenisPenerimaan::select(['id', 'nama_jenis_penerimaan'])->get();
        $asalBarang = GudangDanToko::select(['id', 'nama_gudang_toko'])
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
                'barangs' => $barangs,
                'jenisPenerimaan' => $jenisPenerimaan,
                'asalBarang' => $asalBarang,
                'satuanBerat' => $satuanBerat,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
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
                $penerimaanDiPusat = PenerimaanDiPusat::create($validated);

                return response()->json([
                    'status' => true,
                    'message' => 'Data Penerimaan Di Pusat berhasil ditambahkan',
                    'data' => $penerimaanDiPusat,
                ]);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Gagal menambahkan Data Penerimaan Di Pusat. Silakan coba lagi. {$th->getMessage()}",
            ]);
        }
    }

    public function show(string $id)
    {
        try{
            $penerimaanDiPusat = PenerimaanDiPusat::with(
                'jenisPenerimaan:id,nama_jenis_penerimaan', 
                'asalBarang:id,nama_gudang_toko', 
                'barang:id,nama_barang', 
                'satuanBerat:id,nama_satuan_berat'
            )->findOrFail($id, [
                'id', 'id_barang',
                'id_jenis_penerimaan', 'id_asal_barang', 
                'id_satuan_berat', 'berat_satuan_barang', 
                'jumlah_barang', 'tanggal'
            ]);

            return response()->json([
                'success' => true,
                'message' => "Data Penerimaan Di Pusat {$id}",
                'data' => $penerimaanDiPusat
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Data Penerimaan Di Pusat dengan ID: {$id} tidak ditemukan.",
            ]);
        }
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {

    }

    public function destroy(string $id)
    {
        try {
            $penerimaanDiPusat = PenerimaanDiPusat::findOrFail($id);

            if ($penerimaanDiPusat->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Data Penerimaan Di Pusat dengan ID: {$id} sudah dihapus sebelumnya",
                ]);
            }

            return DB::transaction(function () use ($id, $penerimaanDiPusat) {
                $penerimaanDiPusat->update(['flag' => 0]);

                return response()->json([
                    'status' => true,
                    'message' => "Data Penerimaan Di Pusat dengan ID: {$id} berhasil dihapus",
                ]);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Gagal menghapus Data Penerimaan Di Pusat dengan ID: {$id}. Silakan coba lagi.",
            ]);
        }
    }
}
