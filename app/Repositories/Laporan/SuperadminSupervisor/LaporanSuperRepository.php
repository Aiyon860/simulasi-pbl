<?php

namespace App\Repositories\Laporan\SuperadminSupervisor;

use App\Models\CabangKePusat;
use App\Models\PusatKeSupplier;
use Carbon\Carbon;
use App\Models\CabangKeToko;
use App\Models\TokoKeCabang;
use App\Models\PusatKeCabang;
use App\Models\PenerimaanDiPusat;
use App\Models\PenerimaanDiCabang;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LaporanSuperRepository
{
    private function getBaseLaporanHarianQuery($model, $jenisPenerimaan = null, $useJenisPenerimaan = true)
    {
        $query = $model::select(
            DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d %H:%i:%s') as jam_grup"),
            DB::raw("COUNT(*) as total")
        );

        if ($useJenisPenerimaan && $jenisPenerimaan) {
            $query->whereHas('jenisPenerimaan', function ($query) use ($jenisPenerimaan) {
                $query->where('nama_jenis_penerimaan', $jenisPenerimaan);
            });
        }

        return $query;
    }

    public function getLaporanHarian($model, $jenisPenerimaan = null, $useJenisPenerimaan = true): Collection
    {
        return $this->getBaseLaporanHarianQuery($model, $jenisPenerimaan, $useJenisPenerimaan)
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
}