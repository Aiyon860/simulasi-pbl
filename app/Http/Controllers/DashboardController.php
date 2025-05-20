<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
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
                    'jumlah_stok_seluruh_gudang' => (int) $detailGudangBarangCount,
                    'jumlah_gudang' => $gudangCount,
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
                            // description
                            $description = TimeHelpers::getHoursUntilNow();

                            // data (array of from 00.00 to now)
                            // laporan masuk pengiriman
                            $laporanMasukPengirimanPusat = PenerimaanDiPusat::select(
                                        DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d %H:00:00') as jam_grup"),
                                        DB::raw("COUNT(*) as total")
                                    )
                                    ->whereHas('jenisPenerimaan', function ($query) {
                                        $query->where('nama_jenis_penerimaan', 'pengiriman');
                                    })
                                    ->whereDate('tanggal', Carbon::today())
                                    ->groupBy(DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d %H:00:00')"))
                                    ->get();

                            $laporanMasukPengirimanCabang = PenerimaanDiCabang::select(
                                        DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d %H:00:00') as jam_grup"),
                                        DB::raw("COUNT(*) as total")
                                    )
                                    ->whereHas('jenisPenerimaan', function ($query) {
                                        $query->where('nama_jenis_penerimaan', 'pengiriman');
                                    })
                                    ->whereDate('tanggal', Carbon::today())
                                    ->groupBy(DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d %H:00:00')"))
                                    ->get();

                            $laporanMasukPengirimanGabungan = $laporanMasukPengirimanPusat->concat($laporanMasukPengirimanCabang);

                            $laporanMasukPengiriman = $laporanMasukPengirimanGabungan->groupBy('jam_grup')
                                    ->map(function ($grouped) {
                                        return [
                                            'jam_label' => TimeHelpers::jamInterval($grouped->first()->jam_grup),
                                            'total' => $grouped->sum('total'),
                                        ];
                                    })
                                    ->sortBy('jam_label') // sort optional
                                    ->values(); // reset index

                            // laporan massuk retur
                            $laporanReturPengirimanPusat = PenerimaanDiPusat::select(
                                        DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d %H:00:00') as jam_grup"),
                                        DB::raw("COUNT(*) as total")
                                    )
                                    ->whereHas('jenisPenerimaan', function ($query) {
                                        $query->where('nama_jenis_penerimaan', 'retur');
                                    })
                                    ->whereDate('tanggal', Carbon::today())
                                    ->groupBy(DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d %H:00:00')"))
                                    ->get();

                            $laporanReturPengirimanCabang = PenerimaanDiCabang::select(
                                        DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d %H:00:00') as jam_grup"),
                                        DB::raw("COUNT(*) as total")
                                    )
                                    ->whereHas('jenisPenerimaan', function ($query) {
                                        $query->where('nama_jenis_penerimaan', 'retur');
                                    })
                                    ->whereDate('tanggal', Carbon::today())
                                    ->groupBy(DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d %H:00:00')"))
                                    ->get();

                            $laporanReturPengirimanGabungan = $laporanReturPengirimanPusat->concat($laporanReturPengirimanCabang);

                            $laporanMasukRetur = $laporanReturPengirimanGabungan->groupBy('jam_grup')
                                    ->map(function ($grouped) {
                                        return [
                                            'jam_label' => TimeHelpers::jamInterval($grouped->first()->jam_grup),
                                            'total' => $grouped->sum('total'),
                                        ];
                                    })
                                    ->sortBy('jam_label') // sort optional
                                    ->values();

                            // laporan keluar
                            $laporanKeluarDariPusat = PusatKeCabang::select(
                                                DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d %H:00:00') as jam_grup"),
                                                        DB::raw("COUNT(*) as total")
                                                    )
                                                    ->whereDate('tanggal', Carbon::today())
                                                    ->groupBy(DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d %H:00:00')"))
                                                    ->orderBy('jam_grup')
                                                    ->get();

                            $laporanKeluarDariCabang = CabangKeToko::select(
                                                DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d %H:00:00') as jam_grup"),
                                                        DB::raw("COUNT(*) as total")
                                                    )
                                                    ->whereDate('tanggal', Carbon::today())
                                                    ->groupBy(DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d %H:00:00')"))
                                                    ->orderBy('jam_grup')
                                                    ->get();

                            $laporanKeluarGabungan = $laporanKeluarDariPusat->concat($laporanKeluarDariCabang);

                            $laporanKeluar = $laporanKeluarGabungan->groupBy('jam_grup')
                                    ->map(function ($grouped) {
                                        return [
                                            'jam_label' => TimeHelpers::jamInterval($grouped->first()->jam_grup),
                                            'total' => $grouped->sum('total'),
                                        ];
                                    })
                                    ->sortBy('jam_label') // sort optional
                                    ->values();

                            $laporanReturKeSupplier = PusatKeSupplier::select(
                                                DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d %H:00:00') as jam_grup"),
                                                        DB::raw("COUNT(*) as total")
                                                    )
                                                    ->whereDate('tanggal', Carbon::today())
                                                    ->groupBy(DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d %H:00:00')"))
                                                    ->orderBy('jam_grup')
                                                    ->get();

                            $laporanReturKePusat = CabangKePusat::select(
                                                DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d %H:00:00') as jam_grup"),
                                                        DB::raw("COUNT(*) as total")
                                                    )
                                                    ->whereDate('tanggal', Carbon::today())
                                                    ->groupBy(DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d %H:00:00')"))
                                                    ->orderBy('jam_grup')
                                                    ->get();

                            $laporanReturKeCabang = TokoKeCabang::select(
                                                DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d %H:00:00') as jam_grup"),
                                                        DB::raw("COUNT(*) as total")
                                                    )
                                                    ->whereDate('tanggal', Carbon::today())
                                                    ->groupBy(DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d %H:00:00')"))
                                                    ->orderBy('jam_grup')
                                                    ->get();

                            $laporanReturGabungan = $laporanReturKeSupplier->concat($laporanReturKePusat)->concat($laporanReturKeCabang);

                            $laporanRetur = $laporanReturGabungan->groupBy('jam_grup')
                                    ->map(function ($grouped) {
                                        return [
                                            'jam_label' => TimeHelpers::jamInterval($grouped->first()->jam_grup),
                                            'total' => $grouped->sum('total'),
                                        ];
                                    })
                                    ->sortBy('jam_label') // sort optional
                                    ->values();

                            break;
                        case '1 minggu yang lalu':
                            // description
                            $description = TimeHelpers::getLastSevenDays();

                            // data
                            break;
                        default:    // 1 bulan yang lalu
                            // description
                            $description = TimeHelpers::getFourDatesFromLastMonth();

                            // data
                            break;
                    }
                } else {    // admin cabang
                    $user = $request->user();
                    if (!$user->lokasi) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Pengguna ini tidak terhubung dengan informasi gudang atau toko.',
                        ], 400); // Bad Request
                    }
                    $idGudangAdmin = $user->lokasi->id;

                    switch ($graphRequest) {
                        case 'Hari ini':
                            // description
                            $description = TimeHelpers::getHoursUntilNow();

                            // data (array of from 00.00 to now)
                            // laporan masuk pengiriman
                            $laporanMasukPengirimanCabang = PenerimaanDiCabang::select(
                                        DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d %H:00:00') as jam_grup"),
                                        DB::raw("COUNT(*) as total")
                                    )
                                    ->whereHas('jenisPenerimaan', function ($query) {
                                        $query->where('nama_jenis_penerimaan', 'pengiriman');
                                    })
                                    ->where('id_cabang', $idGudangAdmin)
                                    ->whereDate('tanggal', Carbon::today())
                                    ->groupBy(DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d %H:00:00')"))
                                    ->get();

                            $laporanMasukPengiriman = $laporanMasukPengirimanCabang->groupBy('jam_grup')
                                    ->map(function ($grouped) {
                                        return [
                                            'jam_label' => TimeHelpers::jamInterval($grouped->first()->jam_grup),
                                            'total' => $grouped->sum('total'),
                                        ];
                                    })
                                    ->sortBy('jam_label') // sort optional
                                    ->values(); // reset index

                            // laporan massuk retur
                            $laporanReturPengirimanCabang = $dataCabang = PenerimaanDiCabang::select(
                                        DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d %H:00:00') as jam_grup"),
                                        DB::raw("COUNT(*) as total")
                                    )
                                    ->whereHas('jenisPenerimaan', function ($query) {
                                        $query->where('nama_jenis_penerimaan', 'retur');
                                    })
                                    ->where('id_cabang', $idGudangAdmin)
                                    ->whereDate('tanggal', Carbon::today())
                                    ->groupBy(DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d %H:00:00')"))
                                    ->get();

                            $laporanMasukRetur = $laporanReturPengirimanCabang->groupBy('jam_grup')
                                    ->map(function ($grouped) {
                                        return [
                                            'jam_label' => TimeHelpers::jamInterval($grouped->first()->jam_grup),
                                            'total' => $grouped->sum('total'),
                                        ];
                                    })
                                    ->sortBy('jam_label') // sort optional
                                    ->values();

                            // laporan keluar
                            $laporanKeluarDariCabang = CabangKeToko::select(
                                                DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d %H:00:00') as jam_grup"),
                                                        DB::raw("COUNT(*) as total")
                                                    )
                                                    ->whereDate('tanggal', Carbon::today())
                                                    ->where('id_cabang', $idGudangAdmin)
                                                    ->groupBy(DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d %H:00:00')"))
                                                    ->orderBy('jam_grup')
                                                    ->get();

                            $laporanKeluar = $laporanKeluarDariCabang->groupBy('jam_grup')
                                    ->map(function ($grouped) {
                                        return [
                                            'jam_label' => TimeHelpers::jamInterval($grouped->first()->jam_grup),
                                            'total' => $grouped->sum('total'),
                                        ];
                                    })
                                    ->sortBy('jam_label') // sort optional
                                    ->values();

                            $laporanReturKePusat = CabangKePusat::select(
                                                DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d %H:00:00') as jam_grup"),
                                                        DB::raw("COUNT(*) as total")
                                                    )
                                                    ->whereDate('tanggal', Carbon::today())
                                                    ->where('id_cabang', $idGudangAdmin)
                                                    ->groupBy(DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d %H:00:00')"))
                                                    ->orderBy('jam_grup')
                                                    ->get();

                            $laporanRetur = $laporanReturKePusat->groupBy('jam_grup')
                                    ->map(function ($grouped) {
                                        return [
                                            'jam_label' => TimeHelpers::jamInterval($grouped->first()->jam_grup),
                                            'total' => $grouped->sum('total'),
                                        ];
                                    })
                                    ->sortBy('jam_label') // sort optional
                                    ->values();

                            break;
                        case '1 minggu yang lalu':
                            // description
                            $description = TimeHelpers::getLastSevenDays();

                            // data
                            break;
                        default:    // 1 bulan yang lalu
                            // description
                            $description = TimeHelpers::getFourDatesFromLastMonth();

                            // data
                            // laporan masuk pengiriman
                            $laporanMasukPengiriman = PenerimaanDiCabang::select(
                                    DB::raw("YEARWEEK(tanggal, 1) as minggu_ke"),
                                    DB::raw("COUNT(*) as total")
                                )
                                ->whereHas('jenisPenerimaan', function ($query) {
                                    $query->where('nama_jenis_penerimaan', 'pengiriman');
                                })
                                ->whereBetween('tanggal', [Carbon::now()->subMonth()->startOfDay(), Carbon::now()->endOfDay()])
                                ->groupBy(DB::raw("YEARWEEK(tanggal, 1)"))
                                ->get()
                                ->map(function ($item) {
                                    return [
                                        'jam_label' => "Minggu ke-" . substr($item->minggu_ke, 4), // e.g., 202519 => "Minggu ke-19"
                                        'total' => $item->total,
                                    ];
                                });

                            // laporan masuk retur
                            $laporanMasukRetur = PenerimaanDiCabang::select(
                                    DB::raw("YEARWEEK(tanggal, 1) as minggu_ke"),
                                    DB::raw("COUNT(*) as total")
                                )
                                ->whereHas('jenisPenerimaan', function ($query) {
                                    $query->where('nama_jenis_penerimaan', 'retur');
                                })
                                ->whereBetween('tanggal', [Carbon::now()->subMonth()->startOfDay(), Carbon::now()->endOfDay()])
                                ->groupBy(DB::raw("YEARWEEK(tanggal, 1)"))
                                ->get()
                                ->map(function ($item) {
                                    return [
                                        'jam_label' => "Minggu ke-" . substr($item->minggu_ke, 4),
                                        'total' => $item->total,
                                    ];
                                });

                            // laporan keluar
                            $laporanKeluar = CabangKeToko::select(
                                    DB::raw("YEARWEEK(tanggal, 1) as minggu_ke"),
                                    DB::raw("COUNT(*) as total")
                                )
                                ->whereBetween('tanggal', [Carbon::now()->subMonth()->startOfDay(), Carbon::now()->endOfDay()])
                                ->groupBy(DB::raw("YEARWEEK(tanggal, 1)"))
                                ->get()
                                ->map(function ($item) {
                                    return [
                                        'jam_label' => "Minggu ke-" . substr($item->minggu_ke, 4),
                                        'total' => $item->total,
                                    ];
                                });

                            // laporan retur (retur ke pusat)
                            $laporanRetur = CabangKePusat::select(
                                    DB::raw("YEARWEEK(tanggal, 1) as minggu_ke"),
                                    DB::raw("COUNT(*) as total")
                                )
                                ->whereBetween('tanggal', [Carbon::now()->subMonth()->startOfDay(), Carbon::now()->endOfDay()])
                                ->groupBy(DB::raw("YEARWEEK(tanggal, 1)"))
                                ->get()
                                ->map(function ($item) {
                                    return [
                                        'jam_label' => "Minggu ke-" . substr($item->minggu_ke, 4),
                                        'total' => $item->total,
                                    ];
                                });
                            break;
                    }
                }

                $result = [
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
