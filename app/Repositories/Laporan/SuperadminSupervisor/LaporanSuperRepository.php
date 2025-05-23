<?php

namespace App\Repositories\Laporan\SuperadminSupervisor;

use App\Models\CabangKePusat;
use App\Models\PusatKeSupplier;
use App\Repositories\Laporan\LaporanRepository;
use Carbon\Carbon;
use App\Models\CabangKeToko;
use App\Models\TokoKeCabang;
use App\Models\PusatKeCabang;
use App\Models\PenerimaanDiPusat;
use App\Models\PenerimaanDiCabang;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LaporanSuperRepository extends LaporanRepository
{
    // Harian
    public function getLaporanHarian($model, $jenisPenerimaan = null, $useJenisPenerimaan = true): Collection
    {
        return $this->getBaseLaporanQuery($model, "jam_grup", "%Y-%m-%d %H:%i:%s", $jenisPenerimaan, $useJenisPenerimaan)
            ->whereBetween('tanggal', [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()])
            ->groupBy(DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d %H:%i:%s')"))
            ->get();
    }

    public function getLaporanMasukPengirimanHarian(): Collection
    {
        $pusatData = $this->getLaporanHarian(PenerimaanDiPusat::class, 'pengiriman');
        $cabangData = $this->getLaporanHarian(PenerimaanDiCabang::class, 'pengiriman');
        
        return $pusatData->concat($cabangData);
    }

    public function getLaporanMasukReturHarian(): Collection
    {
        $pusatData = $this->getLaporanHarian(PenerimaanDiPusat::class, 'retur');
        $cabangData = $this->getLaporanHarian(PenerimaanDiCabang::class, 'retur');
        
        return $pusatData->concat($cabangData);
    }

    public function getLaporanKeluarHarian(): Collection
    {
        $pusatKeCabangData = $this->getLaporanHarian(PusatKeCabang::class, null, false);
        $cabangKeTokoData = $this->getLaporanHarian(CabangKeToko::class, null, false);
        
        return $pusatKeCabangData->concat($cabangKeTokoData);
    }

    public function getLaporanReturHarian(): Collection
    {
        $tokoKeCabangData = $this->getLaporanHarian(TokoKeCabang::class, null, false);
        $cabangKePusatData = $this->getLaporanHarian(CabangKePusat::class, null, false);
        $pusatKeSupplierData = $this->getLaporanHarian(PusatKeSupplier::class, null, false);
        
        return $tokoKeCabangData->concat($cabangKePusatData)->concat($pusatKeSupplierData);
    }

    // Mingguan
    public function getLaporanMingguan($model, $jenisPenerimaan = null, $useJenisPenerimaan = true): Collection
    {
        return $this->getBaseLaporanQuery($model, "hari_grup", "%Y-%m-%d", $jenisPenerimaan, $useJenisPenerimaan)
            ->whereBetween('tanggal', [Carbon::yesterday()->subDays(7)->startOfDay(), Carbon::yesterday()->endOfDay()])
            ->groupBy(DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d')"))
            ->get();
    }

    public function getLaporanMasukPengirimanMingguan(): Collection
    {
        $pusatData = $this->getLaporanMingguan(PenerimaanDiPusat::class, 'pengiriman');
        $cabangData = $this->getLaporanMingguan(PenerimaanDiCabang::class, 'pengiriman');
        
        return $pusatData->concat($cabangData);
    }

    public function getLaporanMasukReturMingguan(): Collection
    {
        $pusatData = $this->getLaporanMingguan(PenerimaanDiPusat::class, 'retur');
        $cabangData = $this->getLaporanMingguan(PenerimaanDiCabang::class, 'retur');
        
        return $pusatData->concat($cabangData);
    }

    public function getLaporanKeluarMingguan(): Collection
    {
        $pusatKeCabangData = $this->getLaporanMingguan(PusatKeCabang::class, null, false);
        $cabangKeTokoData = $this->getLaporanMingguan(CabangKeToko::class, null, false);
        
        return $pusatKeCabangData->concat($cabangKeTokoData);
    }

    public function getLaporanReturMingguan(): Collection
    {
        $tokoKeCabangData = $this->getLaporanMingguan(TokoKeCabang::class, null, false);
        $cabangKePusatData = $this->getLaporanMingguan(CabangKePusat::class, null, false);
        $pusatKeSupplierData = $this->getLaporanMingguan(PusatKeSupplier::class, null, false);
        
        return $tokoKeCabangData->concat($cabangKePusatData)->concat($pusatKeSupplierData);
    }

    // Bulanan
    public function getLaporanBulanan($model, $jenisPenerimaan = null, $useJenisPenerimaan = true): Collection
    {
        return $this->getBaseLaporanQuery($model, "minggu_grup", "%Y-%m-%d", $jenisPenerimaan, $useJenisPenerimaan)
            ->whereBetween('tanggal', [Carbon::yesterday()->copy()->subMonth()->startOfDay(), Carbon::yesterday()->endOfDay()])
            ->groupBy(DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d')"))
            ->get();
    }

    public function getLaporanMasukPengirimanBulanan(): Collection
    {
        $pusatData = $this->getLaporanBulanan(PenerimaanDiPusat::class, 'pengiriman');
        $cabangData = $this->getLaporanBulanan(PenerimaanDiCabang::class, 'pengiriman');
        
        return $pusatData->concat($cabangData);
    }

    public function getLaporanMasukReturBulanan(): Collection
    {
        $pusatData = $this->getLaporanBulanan(PenerimaanDiPusat::class, 'retur');
        $cabangData = $this->getLaporanBulanan(PenerimaanDiCabang::class, 'retur');
        
        return $pusatData->concat($cabangData);
    }

    public function getLaporanKeluarBulanan(): Collection
    {
        $pusatKeCabangData = $this->getLaporanBulanan(PusatKeCabang::class, null, false);
        $cabangKeTokoData = $this->getLaporanBulanan(CabangKeToko::class, null, false);
        
        return $pusatKeCabangData->concat($cabangKeTokoData);
    }

    public function getLaporanReturBulanan(): Collection
    {
        $tokoKeCabangData = $this->getLaporanBulanan(TokoKeCabang::class, null, false);
        $cabangKePusatData = $this->getLaporanBulanan(CabangKePusat::class, null, false);
        $pusatKeSupplierData = $this->getLaporanBulanan(PusatKeSupplier::class, null, false);
        
        return $tokoKeCabangData->concat($cabangKePusatData)->concat($pusatKeSupplierData);
    }
}