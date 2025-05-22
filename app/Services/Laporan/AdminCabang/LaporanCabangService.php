<?php

namespace App\Services\Laporan\AdminCabang;

use Illuminate\Support\Collection;
use App\Services\Laporan\LaporanService;
use App\Repositories\Laporan\AdminCabang\LaporanCabangRepository;

class LaporanCabangService extends LaporanService
{
    protected $laporanRepository;

    public function __construct(LaporanCabangRepository $laporanRepository)
    {
        $this->laporanRepository = $laporanRepository;
    }

    // Harian
    public function getLaporanMasukPengirimanHarian(int $idGudangAdmin, array $intervals): Collection
    {
        $data = $this->laporanRepository->getLaporanMasukPengirimanHarian($idGudangAdmin);
        return $this->groupLaporanByInterval($data, $intervals);
    }
    
    public function getLaporanMasukReturHarian(int $idGudangAdmin, array $intervals): Collection
    {
        $data = $this->laporanRepository->getLaporanMasukReturHarian($idGudangAdmin);
        return $this->groupLaporanByInterval($data, $intervals);
    }

    public function getLaporanKeluarHarian(int $idGudangAdmin, array $intervals): Collection
    {
        $data = $this->laporanRepository->getLaporanKeluarHarian($idGudangAdmin);
        return $this->groupLaporanByInterval($data, $intervals);
    }

    public function getLaporanReturHarian(int $idGudangAdmin, array $intervals): Collection
    {
        $data = $this->laporanRepository->getLaporanReturHarian($idGudangAdmin);
        return $this->groupLaporanByInterval($data, $intervals);
    }

    // Mingguan
    public function getLaporanMasukPengirimanMingguan(int $idGudangAdmin, array $intervals): Collection
    {
        $data = $this->laporanRepository->getLaporanMasukPengirimanMingguan($idGudangAdmin);
        return $this->groupLaporanByInterval($data, $intervals, "hari");
    }

    public function getLaporanMasukReturMingguan(int $idGudangAdmin, array $intervals): Collection
    {
        $data = $this->laporanRepository->getLaporanMasukReturMingguan($idGudangAdmin);
        return $this->groupLaporanByInterval($data, $intervals, "hari");
    }

    public function getLaporanKeluarMingguan(int $idGudangAdmin, array $intervals): Collection
    {
        $data = $this->laporanRepository->getLaporanKeluarMingguan($idGudangAdmin);
        return $this->groupLaporanByInterval($data, $intervals, "hari");
    }

    public function getLaporanReturMingguan(int $idGudangAdmin, array $intervals): Collection
    {
        $data = $this->laporanRepository->getLaporanReturMingguan($idGudangAdmin);
        return $this->groupLaporanByInterval($data, $intervals, "hari");
    }

    // Bulanan
    public function getLaporanMasukPengirimanBulanan(int $idGudangAdmin, array $intervals): Collection
    {
        $data = $this->laporanRepository->getLaporanMasukPengirimanBulanan($idGudangAdmin);
        return $this->groupLaporanByInterval($data, $intervals, "minggu");
    }

    public function getLaporanMasukReturBulanan(int $idGudangAdmin, array $intervals): Collection
    {
        $data = $this->laporanRepository->getLaporanMasukReturBulanan($idGudangAdmin);
        return $this->groupLaporanByInterval($data, $intervals, "minggu");
    }

    public function getLaporanKeluarBulanan(int $idGudangAdmin, array $intervals): Collection
    {
        $data = $this->laporanRepository->getLaporanKeluarBulanan($idGudangAdmin);
        return $this->groupLaporanByInterval($data, $intervals, "minggu");
    }

    public function getLaporanReturBulanan(int $idGudangAdmin, array $intervals): Collection
    {
        $data = $this->laporanRepository->getLaporanReturBulanan($idGudangAdmin);
        return $this->groupLaporanByInterval($data, $intervals, "minggu");
    }
}