<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Barang;
use App\Helpers\TimeHelpers;
use App\Models\CabangKeToko;
use App\Models\DetailGudang;
use App\Models\TokoKeCabang;
use Illuminate\Http\Request;
use App\Models\CabangKePusat;
use App\Models\GudangDanToko;
use App\Models\PusatKeCabang;
use App\Models\KategoriBarang;
use App\Models\PusatKeSupplier;
use App\Models\PenerimaanDiPusat;
use App\Models\PenerimaanDiCabang;
use Illuminate\Support\Facades\DB;
use App\Services\StokBarang\StokBarangService;
use App\Services\Laporan\AdminCabang\LaporanCabangService;
use App\Services\Laporan\SuperadminSupervisor\LaporanSuperService;

class DashboardController extends Controller
{
    protected $laporanSuperService, $laporanCabangService, $stokBarangService;

    public function __construct(LaporanSuperService $laporanSuperService, LaporanCabangService $laporanCabangService, StokBarangService $stokBarangService)
    {
        $this->laporanSuperService = $laporanSuperService;
        $this->laporanCabangService = $laporanCabangService;
        $this->stokBarangService = $stokBarangService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $result = [];

            if ($request->user()->hasRole('SuperAdmin', 'Supervisor')) {
                $categoriesCount = KategoriBarang::count();
                $barangCount = Barang::count();
                $gudangCount = GudangDanToko::where('kategori_bangunan', 0)->count();
                $supplierCount = GudangDanToko::where('kategori_bangunan', 1)->count();
                $tokoCount = GudangDanToko::where('kategori_bangunan', 2)->count();

                $laporanMasukPengirimanCount = PenerimaanDiPusat::whereHas('jenisPenerimaan', function ($query) {
                    $query->where('nama_jenis_penerimaan', 'pengiriman');
                })->count();
                $laporanMasukPengirimanCount += PenerimaanDiCabang::whereHas('jenisPenerimaan', function ($query) {
                    $query->where('nama_jenis_penerimaan', 'pengiriman');
                })->count();

                $laporanMasukReturCount = PenerimaanDiPusat::whereHas('jenisPenerimaan', function ($query) {
                    $query->where('nama_jenis_penerimaan', 'retur');
                })->count();
                $laporanMasukReturCount += PenerimaanDiCabang::whereHas('jenisPenerimaan', function ($query) {
                    $query->where('nama_jenis_penerimaan', 'retur');
                })->count();

                $laporanKeluarCount = PusatKeCabang::count();
                $laporanKeluarCount += CabangKeToko::count();

                $laporanReturCount = TokoKeCabang::count();
                $laporanReturCount += CabangKePusat::count();
                $laporanReturCount += PusatKeSupplier::count();

                $result = [
                    'jumlah_kategori' => $categoriesCount,
                    'jumlah_stok_seluruh_gudang' => (int) $barangCount,
                    'jumlah_gudang' => $gudangCount,
                    'jumlah_supplier' => $supplierCount,
                    'jumlah_toko' => $tokoCount,
                    'jumlah_laporan_masuk_pengiriman' => $laporanMasukPengirimanCount,
                    'jumlah_laporan_masuk_retur' => $laporanMasukReturCount,
                    'jumlah_laporan_keluar' => (int) $laporanKeluarCount,
                    'jumlah_laporan_retur' => (int) $laporanReturCount,
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
                $laporanMasukPengirimanCount = PenerimaanDiCabang::where('id_cabang', $idGudangAdmin)->whereHas('jenisPenerimaan', function ($query) {
                    $query->where('nama_jenis_penerimaan', 'pengiriman');
                })->count();
                $laporanMasukReturCount = PenerimaanDiCabang::where('id_cabang', $idGudangAdmin)->whereHas('jenisPenerimaan', function ($query) {
                    $query->where('nama_jenis_penerimaan', 'retur');
                })->count();
                $laporanKeluarCount = CabangKeToko::where('id_cabang', $idGudangAdmin)->count();
                $laporanReturCount = CabangKePusat::where('id_cabang', $idGudangAdmin)->count();

                $result = [
                    'id_lokasi' => $user->lokasi->id,
                    'jumlah_barang' => $stokSemuaBarang,
                    'jumlah_laporan_masuk_pengiriman' => $laporanMasukPengirimanCount,
                    'jumlah_laporan_masuk_retur' => $laporanMasukReturCount,
                    'jumlah_laporan_keluar' => $laporanKeluarCount,
                    'jumlah_laporan_retur' => $laporanReturCount,
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
        try {
            $result = [];

            if ($request->filled('filter_durasi')) {
                $graphRequest = $request->input('filter_durasi');

                $laporanMasukPengiriman = [];
                $laporanMasukRetur = [];
                $laporanKeluar = [];
                $laporanRetur = [];

                $description = [];

                if ($request->user()->hasRole('SuperAdmin', 'Supervisor')) {
                    switch ($graphRequest) {
                        case 'Hari ini':
                            $description = TimeHelpers::getHoursUntilNow();
                            $intervals = TimeHelpers::getHourlyIntervals();

                            $laporanMasukPengiriman = $this->laporanSuperService->getLaporanMasukPengirimanHarian($intervals);
                            $laporanMasukRetur = $this->laporanSuperService->getLaporanMasukReturHarian($intervals);
                            $laporanKeluar = $this->laporanSuperService->getLaporanKeluarHarian($intervals);
                            $laporanRetur = $this->laporanSuperService->getLaporanReturHarian($intervals);

                            break;
                        case '1 minggu yang lalu':
                            // description
                            $description = TimeHelpers::getLastSevenDays();
                            $intervals = TimeHelpers::getDailyIntervals();

                            $laporanMasukPengiriman = $this->laporanSuperService->getLaporanMasukPengirimanMingguan($intervals);
                            $laporanMasukRetur = $this->laporanSuperService->getLaporanMasukReturMingguan($intervals);
                            $laporanKeluar = $this->laporanSuperService->getLaporanKeluarMingguan($intervals);
                            $laporanRetur = $this->laporanSuperService->getLaporanReturMingguan($intervals);

                            break;
                        default:    // 1 bulan yang lalu
                            $description = TimeHelpers::getFourDatesFromLastMonth();
                            $intervals = TimeHelpers::getMingguanIntervals();

                            $laporanMasukPengiriman = $this->laporanSuperService->getLaporanMasukPengirimanBulanan($intervals);
                            $laporanMasukRetur = $this->laporanSuperService->getLaporanMasukReturBulanan($intervals);
                            $laporanKeluar = $this->laporanSuperService->getLaporanKeluarBulanan($intervals);
                            $laporanRetur = $this->laporanSuperService->getLaporanReturBulanan($intervals);

                        break;
                    }
                } else {    // admin cabang
                    $user = $request->user();
                    if (!$user->lokasi) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Pengguna ini tidak terhubung dengan gudang manapun.',
                        ], 400); // Bad Request
                    }
                    $idGudangAdmin = $user->lokasi->id;

                    switch ($graphRequest) {
                        case 'Hari ini':
                            $description = TimeHelpers::getHoursUntilNow();
                            $intervals = TimeHelpers::getHourlyIntervals();

                            $laporanMasukPengiriman = $this->laporanCabangService->getLaporanMasukPengirimanHarian($idGudangAdmin, $intervals);
                            $laporanMasukRetur = $this->laporanCabangService->getLaporanMasukReturHarian($idGudangAdmin, $intervals);
                            $laporanKeluar = $this->laporanCabangService->getLaporanKeluarHarian($idGudangAdmin, $intervals);
                            $laporanRetur = $this->laporanCabangService->getLaporanReturHarian($idGudangAdmin, $intervals);

                            break;
                        case '1 minggu yang lalu':
                            // description
                            $description = TimeHelpers::getLastSevenDays();
                            $intervals = TimeHelpers::getDailyIntervals();
                                                                                                            
                            $laporanMasukPengiriman = $this->laporanCabangService->getLaporanMasukPengirimanMingguan($idGudangAdmin, $intervals);
                            $laporanMasukRetur = $this->laporanCabangService->getLaporanMasukReturMingguan($idGudangAdmin, $intervals);
                            $laporanKeluar = $this->laporanCabangService->getLaporanKeluarMingguan($idGudangAdmin, $intervals);
                            $laporanRetur = $this->laporanCabangService->getLaporanReturMingguan($idGudangAdmin, $intervals);

                            break;
                        default:    // 1 bulan yang lalu
                            $description = TimeHelpers::getFourDatesFromLastMonth();
                            $intervals = TimeHelpers::getMingguanIntervals();

                            $laporanMasukPengiriman = $this->laporanCabangService->getLaporanMasukPengirimanBulanan($idGudangAdmin, $intervals);
                            $laporanMasukRetur = $this->laporanCabangService->getLaporanMasukReturBulanan($idGudangAdmin, $intervals);
                            $laporanKeluar = $this->laporanCabangService->getLaporanKeluarBulanan($idGudangAdmin, $intervals);
                            $laporanRetur = $this->laporanCabangService->getLaporanReturBulanan($idGudangAdmin, $intervals);

                            break;
                    }
                }

                $result = [
                    'description' => $description,
                    'laporan_masuk_pengiriman' => $laporanMasukPengiriman,
                    'laporan_masuk_retur' => $laporanMasukRetur,
                    'laporan_keluar' => $laporanKeluar,
                    'laporan_retur' => $laporanRetur,
                ];
            }

            return response()->json([
                'status' => true,
                'message' => "Data Grafik Dashboard untuk {$request->user()->nama_user}.",
                'data' => $result,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data dashboard.',
                'error' => $th->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    public function dashboardLowStock(Request $request)
    {
        if (!$request->user()->lokasi) {
            return response()->json([
                'status' => false,
                'message' => 'Pengguna ini tidak terhubung dengan gudang manapun.',
            ], 400); // Bad Request
        }

        $lokasi = $request->user()->lokasi;

        try {
            $barangs = [];

            if ($request->user()->hasRole('SuperAdmin', 'Supervisor')) {
                // supervisor privilege === superadmin's
                if ($request->user()->hasRole('Supervisor')) {
                    $lokasi->id = 1; 
                }
                $barangs = $this->stokBarangService->getTopTenLowestStockSuper();
            } else { // admin cabang
                $barangs = $this->stokBarangService->getTopTenLowestStockCabang($lokasi->id);
            }
    
            return response()->json([
                'status' => true,
                'message' => "Data barang dengan stok rendah di seluruh gudang.",
                'data' => $barangs,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil data barang dengan stok rendah {$lokasi->nama_gudang_toko}.",
                'error' => $th->getMessage(),
            ], 500); // Internal Server Error
        }
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
