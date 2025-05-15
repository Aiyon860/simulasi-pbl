<?php

namespace App\Http\Controllers;

use App\Models\CabangKeToko;
use App\Models\DetailGudang;
use Illuminate\Http\Request;
use App\Models\CabangKePusat;
use App\Models\GudangDanToko;
use App\Models\PusatKeCabang;
use App\Models\KategoriBarang;
use App\Models\PusatKeSupplier;
use App\Models\PenerimaanDiPusat;
use App\Models\PenerimaanDiCabang;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
    {
        try {
            $result = [];

            if ($request->user()->hasRole('SuperAdmin', 'Supervisor')) {
                $categoriesCount = KategoriBarang::count();
                $detailGudangBarangCount = DetailGudang::sum('jumlah_stok');
                $gudangCount = GudangDanToko::where('kategori_bangunan', 0)->count();
                $tokoCount = GudangDanToko::where('kategori_bangunan', 2)->count();

                $barangMasukPengirimanCount = PenerimaanDiPusat::whereHas('jenisPenerimaan', function ($query) {
                    $query->where('nama_jenis_penerimaan', 'pengiriman');
                })->sum('jumlah_barang');
                $barangMasukPengirimanCount += PenerimaanDiCabang::whereHas('jenisPenerimaan', function ($query) {
                    $query->where('nama_jenis_penerimaan', 'pengiriman');
                })->sum('jumlah_barang');

                $barangMasukReturCount = PenerimaanDiPusat::whereHas('jenisPenerimaan', function ($query) {
                    $query->where('nama_jenis_penerimaan', 'retur');
                })->sum('jumlah_barang');
                $barangMasukReturCount += PenerimaanDiCabang::whereHas('jenisPenerimaan', function ($query) {
                    $query->where('nama_jenis_penerimaan', 'retur');
                })->sum('jumlah_barang');

                $barangKeluarCount = PusatKeCabang::sum('jumlah_barang');
                $barangReturCount = PusatKeSupplier::sum('jumlah_barang');

                $result = [
                    'jumlah_kategori' => $categoriesCount,
                    'jumlah_semua_barang_gudang' => (int) $detailGudangBarangCount,
                    'jumlah_gudang' => $gudangCount,
                    'jumlah_toko' => $tokoCount,
                    'jumlah_total_barang_masuk_pengiriman' => $barangMasukPengirimanCount,
                    'jumlah_total_barang_masuk_retur' => $barangMasukReturCount,
                    'jumlah_total_barang_keluar' => (int) $barangKeluarCount,
                    'jumlah_total_barang_retur' => (int) $barangReturCount,
                ];
            } else {    // admin cabang
                $user = $request->user();
                if (!$user->lokasi) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Pengguna ini tidak terhubung dengan informasi gudang atau toko.',
                    ], 400); // Bad Request
                }
                $idGudangAdmin = $user->lokasi->id;

                $stokSemuaBarang = DetailGudang::where('id_gudang', $idGudangAdmin)->sum('jumlah_stok');
                $barangMasukPengirimanCount = PenerimaanDiCabang::where('id_cabang', $idGudangAdmin)->whereHas('jenisPenerimaan', function ($query) {
                    $query->where('nama_jenis_penerimaan', 'pengiriman');
                })->sum('jumlah_barang');
                $barangMasukReturCount = PenerimaanDiCabang::where('id_cabang', $idGudangAdmin)->whereHas('jenisPenerimaan', function ($query) {
                    $query->where('nama_jenis_penerimaan', 'retur');
                })->sum('jumlah_barang');
                $barangKeluarCount = CabangKeToko::where('id_cabang', $idGudangAdmin)->sum('jumlah_barang');
                $barangReturCount = CabangKePusat::where('id_cabang', $idGudangAdmin)->sum('jumlah_barang');

                $result = [
                    'jumlah_barang' => $stokSemuaBarang,
                    'jumlah_barang_masuk_pengiriman' => $barangMasukPengirimanCount,
                    'jumlah_barang_masuk_retur' => $barangMasukReturCount,
                    'jumlah_barang_keluar' => $barangKeluarCount,
                    'jumlah_barang_retur' => $barangReturCount,
                ];
            }

            return response()->json([
                'status' => true,
                'message' => "Data Dashboard untuk {$request->user()->nama_user}.",
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data dashboard.',
                'error' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    public function dashboardGraph(Request $request)
    {
        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        //
    }
}
