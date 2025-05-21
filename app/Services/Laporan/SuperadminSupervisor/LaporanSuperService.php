<?php

namespace App\Services\Laporan\SuperadminSupervisor;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Repositories\Laporan\SuperadminSupervisor\LaporanSuperRepository;

class LaporanSuperService
{
    protected $laporanRepository;

    public function __construct(LaporanSuperRepository $laporanRepository)
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

    public function getLaporanMasukPengirimanHarian(array $intervals): Collection
    {
        $data = $this->laporanRepository->getLaporanMasukPengirimanHarian();
        return $this->groupLaporanByInterval($data, $intervals);
    }
    
    public function getLaporanMasukReturHarian(array $intervals): Collection
    {
        $data = $this->laporanRepository->getLaporanMasukReturHarian();
        return $this->groupLaporanByInterval($data, $intervals);
    }

    public function getLaporanKeluarHarian(array $intervals): Collection
    {
        $data = $this->laporanRepository->getLaporanKeluarHarian();
        return $this->groupLaporanByInterval($data, $intervals);
    }

    public function getLaporanReturHarian(array $intervals): Collection
    {
        $data = $this->laporanRepository->getLaporanReturHarian();
        return $this->groupLaporanByInterval($data, $intervals);
    }
}