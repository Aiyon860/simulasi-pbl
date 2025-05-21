<?php

namespace App\Repositories\Laporan\AdminCabang;

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

class LaporanCabangRepository
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

    public function getLaporanHarian($model, $idGudangAdmin, $jenisPenerimaan = null, $useJenisPenerimaan = true): Collection
    {
        return $this->getBaseLaporanHarianQuery($model, $jenisPenerimaan, $useJenisPenerimaan)
            ->whereBetween('tanggal', [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()])
            ->where('id_cabang', $idGudangAdmin)
            ->groupBy(DB::raw("DATE_FORMAT(tanggal, '%Y-%m-%d %H:%i:%s')"))
            ->get();
    }

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
}