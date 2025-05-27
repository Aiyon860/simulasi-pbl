<?php

namespace App\Repositories\Laporan\AdminCabang;

use App\Models\CabangKePusat;
use App\Repositories\Laporan\LaporanRepository;
use Carbon\Carbon;
use App\Models\CabangKeToko;
use App\Models\PenerimaanDiCabang;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LaporanCabangRepository extends LaporanRepository
{
    public function getLaporanHarian($model, $idGudangAdmin, $jenisPenerimaan = null, $useJenisPenerimaan = true): Collection
    {
        return $this->getBaseLaporanQuery($model, "jam_grup", "%Y-%m-%d %H:%i:%s", $jenisPenerimaan, $useJenisPenerimaan)
            ->whereBetween('tanggal', [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()])
            ->where('id_cabang', $idGudangAdmin)
            ->groupBy(DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d %H:%i:%s')"))
            ->get();
    }

    // Harian
    public function getLaporanMasukPengirimanHarian($idGudangAdmin): Collection
    {
        return $this->getLaporanHarian(PenerimaanDiCabang::class, $idGudangAdmin, 'pengiriman');
    }

    public function getLaporanMasukReturHarian($idGudangAdmin): Collection
    {
        return $this->getLaporanHarian(PenerimaanDiCabang::class, $idGudangAdmin, 'retur');
    }

    public function getLaporanKeluarHarian($idGudangAdmin): Collection
    {
        return $this->getLaporanHarian(CabangKeToko::class, $idGudangAdmin, null, false);
    }

    public function getLaporanReturHarian($idGudangAdmin): Collection
    {
        return $this->getLaporanHarian(CabangKePusat::class, $idGudangAdmin, null, false);
    }

    // Mingguan
    public function getLaporanMingguan($model, $idGudangAdmin, $jenisPenerimaan = null, $useJenisPenerimaan = true): Collection
    {
        return $this->getBaseLaporanQuery($model, "hari_grup", "%Y-%m-%d", $jenisPenerimaan, $useJenisPenerimaan)
            ->whereBetween('tanggal', [Carbon::yesterday()->subDays(7)->startOfDay(), Carbon::yesterday()->endOfDay()])
            ->where('id_cabang', $idGudangAdmin)
            ->groupBy(DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d')"))
            ->get();
    }

    public function getLaporanMasukPengirimanMingguan($idGudangAdmin): Collection
    {
        return $this->getLaporanMingguan(PenerimaanDiCabang::class, $idGudangAdmin, 'pengiriman');
    }

    public function getLaporanMasukReturMingguan($idGudangAdmin): Collection
    {
        return $this->getLaporanMingguan(PenerimaanDiCabang::class, $idGudangAdmin, 'retur');
    }

    public function getLaporanKeluarMingguan($idGudangAdmin): Collection
    {
        return $this->getLaporanMingguan(CabangKeToko::class, $idGudangAdmin, null, false);
    }

    public function getLaporanReturMingguan($idGudangAdmin): Collection
    {
        return $this->getLaporanMingguan(CabangKePusat::class, $idGudangAdmin, null, false);
    }

    // Bulanan
    public function getLaporanBulanan($model, $idGudangAdmin, $jenisPenerimaan = null, $useJenisPenerimaan = true): Collection
    {
        return $this->getBaseLaporanQuery($model, "minggu_grup", "%Y-%m-%d", $jenisPenerimaan, $useJenisPenerimaan)
            ->whereBetween('tanggal', [Carbon::yesterday()->copy()->subMonth()->startOfDay(), Carbon::yesterday()->endOfDay()])
            ->where('id_cabang', $idGudangAdmin)
            ->groupBy(DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d')"))
            ->get();
    }

    public function getLaporanMasukPengirimanBulanan($idGudangAdmin): Collection
    {
        return $this->getLaporanBulanan(PenerimaanDiCabang::class, $idGudangAdmin, 'pengiriman');
    }

    public function getLaporanMasukReturBulanan($idGudangAdmin): Collection
    {
        return $this->getLaporanBulanan(PenerimaanDiCabang::class, $idGudangAdmin, 'retur');
    }

    public function getLaporanKeluarBulanan($idGudangAdmin): Collection
    {
        return $this->getLaporanBulanan(CabangKeToko::class, $idGudangAdmin, null, false);
    }

    public function getLaporanReturBulanan($idGudangAdmin): Collection
    {
        return $this->getLaporanBulanan(CabangKePusat::class, $idGudangAdmin, null, false);
    }
}