<?php

namespace App\Services\Laporan\SuperadminSupervisor;

use Illuminate\Support\Collection;
use App\Services\Laporan\LaporanService;
use App\Repositories\Laporan\SuperadminSupervisor\LaporanSuperRepository;

class LaporanSuperService extends LaporanService
{
    protected $laporanRepository;

    public function __construct(LaporanSuperRepository $laporanRepository)
    {
        $this->laporanRepository = $laporanRepository;
    }

    // Harian
    public function getLaporanMasukPengirimanHarian(array $intervals): Collection
    {
        $data = $this->laporanRepository->getLaporanMasukPengirimanHarian();
        return $this->groupLaporanByInterval($data, $intervals, "jam");
    }
    
    public function getLaporanMasukReturHarian(array $intervals): Collection
    {
        $data = $this->laporanRepository->getLaporanMasukReturHarian();
        return $this->groupLaporanByInterval($data, $intervals, "jam");
    }

    public function getLaporanKeluarHarian(array $intervals): Collection
    {
        $data = $this->laporanRepository->getLaporanKeluarHarian();
        return $this->groupLaporanByInterval($data, $intervals, "jam");
    }

    public function getLaporanReturHarian(array $intervals): Collection
    {
        $data = $this->laporanRepository->getLaporanReturHarian();
        return $this->groupLaporanByInterval($data, $intervals, "jam");
    }

    // Mingguan
    public function getLaporanMasukPengirimanMingguan(array $intervals): Collection
    {
        $data = $this->laporanRepository->getLaporanMasukPengirimanMingguan();
        return $this->groupLaporanByInterval($data, $intervals, "hari");
    }

    public function getLaporanMasukReturMingguan(array $intervals): Collection
    {
        $data = $this->laporanRepository->getLaporanMasukReturMingguan();
        return $this->groupLaporanByInterval($data, $intervals, "hari");
    }

    public function getLaporanKeluarMingguan(array $intervals): Collection
    {
        $data = $this->laporanRepository->getLaporanKeluarMingguan();
        return $this->groupLaporanByInterval($data, $intervals, "hari");
    }

    public function getLaporanReturMingguan(array $intervals): Collection
    {
        $data = $this->laporanRepository->getLaporanReturMingguan();
        return $this->groupLaporanByInterval($data, $intervals, "hari");
    }

    // Bulanan
    public function getLaporanMasukPengirimanBulanan(array $intervals): Collection
    {
        $data = $this->laporanRepository->getLaporanMasukPengirimanBulanan();
        return $this->groupLaporanByInterval($data, $intervals, "minggu");
    }

    public function getLaporanMasukReturBulanan(array $intervals): Collection
    {
        $data = $this->laporanRepository->getLaporanMasukReturBulanan();
        return $this->groupLaporanByInterval($data, $intervals, "minggu");
    }

    public function getLaporanKeluarBulanan(array $intervals): Collection
    {
        $data = $this->laporanRepository->getLaporanKeluarBulanan();
        return $this->groupLaporanByInterval($data, $intervals, "minggu");
    }

    public function getLaporanReturBulanan(array $intervals): Collection
    {
        $data = $this->laporanRepository->getLaporanReturBulanan();
        return $this->groupLaporanByInterval($data, $intervals, "minggu");
    }
}