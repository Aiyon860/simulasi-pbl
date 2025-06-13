<?php

namespace App\Http\Controllers;

use App\Http\Resources\LaporanDetailsResource;
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
use App\Http\Resources\BarangIndexResource;
use App\Services\StokBarang\StokBarangService;
use App\Http\Resources\DashboardLowStockAdminResource;
use App\Http\Resources\DashboardLowStockSuperResource;
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

    public function dashboardSuper(Request $request)
    {
        try {
            $result = [];

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
                'jumlah_kategori' => (int) $categoriesCount,
                'jumlah_stok_seluruh_gudang' => (int) $barangCount,
                'jumlah_gudang' => (int) $gudangCount,
                'jumlah_supplier' => (int) $supplierCount,
                'jumlah_toko' => (int) $tokoCount,
                'jumlah_laporan_masuk_pengiriman' => (int) $laporanMasukPengirimanCount,
                'jumlah_laporan_masuk_retur' => (int) $laporanMasukReturCount,
                'jumlah_laporan_keluar' => (int) $laporanKeluarCount,
                'jumlah_laporan_retur' => (int) $laporanReturCount,
            ];

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

    public function dashboardAdminCabang(Request $request) {
        try {
            $result = [];

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
                'id_lokasi' => (int) $user->lokasi->id,
                'jumlah_barang' => (int) $stokSemuaBarang,
                'jumlah_laporan_masuk_pengiriman' => (int) $laporanMasukPengirimanCount,
                'jumlah_laporan_masuk_retur' => (int) $laporanMasukReturCount,
                'jumlah_laporan_keluar' => (int) $laporanKeluarCount,
                'jumlah_laporan_retur' => (int) $laporanReturCount,
            ];

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
                    /** @var array<string> */
                    'description' => $description,
                    'laporan_masuk_pengiriman' => LaporanDetailsResource::collection($laporanMasukPengiriman),
                    'laporan_masuk_retur' => LaporanDetailsResource::collection($laporanMasukRetur),
                    'laporan_keluar' => LaporanDetailsResource::collection($laporanKeluar),
                    'laporan_retur' => LaporanDetailsResource::collection($laporanRetur),
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

    public function dashboardLowStockSuper(Request $request)
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

            // supervisor privilege === superadmin's
            if ($request->user()->hasRole('Supervisor')) {
                $lokasi->id = 1;
            }
            $barangs = $this->stokBarangService->getTopTenLowestStockSuper();

            return response()->json([
                'status' => true,
                'message' => "Data barang dengan stok rendah di seluruh gudang.",
                'data' => DashboardLowStockSuperResource::collection($barangs),
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil data barang dengan stok rendah di {$lokasi->nama_gudang_toko}.",
                'error' => $th->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    public function dashboardLowStockAdminCabang(Request $request)
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

            $barangs = $this->stokBarangService->getTopTenLowestStockCabang($lokasi->id);

            return response()->json([
                'status' => true,
                'message' => "Data barang dengan stok rendah di gudang {$request->user()->lokasi->nama_gudang_toko}",
                'data' => DashboardLowStockAdminResource::collection($barangs),
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil data barang dengan stok rendah di {$lokasi->nama_gudang_toko}.",
                'error' => $th->getMessage(),
            ], 500); // Internal Server Error
        }
    }
}
