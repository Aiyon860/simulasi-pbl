<?php

namespace App\Services\Laporan\AdminCabang;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Repositories\Laporan\AdminCabang\LaporanCabangRepository;

class LaporanCabangService
{
    protected $laporanRepository;

    public function __construct(LaporanCabangRepository $laporanRepository)
    {
        $this->laporanRepository = $laporanRepository;
    }

    public function groupLaporanByInterval(Collection $data, array $intervals): Collection
    {
        return collect($intervals)->map(function ($interval) use ($data) {
            $matchingData = $data->filter(function ($item) use ($interval) {
                $itemTime = Carbon::parse($item->jam_grup);
                return $itemTime->format('H:00') === $interval['start']->format('H:00');
            });

            return [
                'jam_label' => $interval['label'],
                'total' => $matchingData->sum('total')
            ];
        });
    }

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
}