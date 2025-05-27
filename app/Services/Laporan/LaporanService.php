<?php

namespace App\Services\Laporan;

use Carbon\Carbon;
use App\Helpers\TimeHelpers;
use Illuminate\Support\Collection;

class LaporanService 
{
    public function groupLaporanByInterval(Collection $data, array $intervals, string $format = 'jam'): Collection
    {
        return collect($intervals)->map(function ($interval, $index) use ($data, $format) {
            $matchingData = $data->filter(function ($item) use ($interval, $format) {
                $itemTime = null;

                if ($format === 'jam') {
                    $itemTime = Carbon::parse($item->jam_grup);

                    return $itemTime->format('H:00') === $interval['start']->format('H:00');
                } else if ($format === "hari") {
                    $itemTime = Carbon::parse($item->hari_grup);

                    return $itemTime->format('Y-m-d') === $interval['start']->format('Y-m-d');
                } else { // minggu
                    $itemTime = Carbon::parse($item->minggu_grup);

                    return $itemTime->betweenIncluded($interval['start'], $interval['end']);
                }    
            });

            $label = match($format) {
                'jam' => $interval['label'],
                'minggu' => sprintf(
                    "Minggu ke-%d (%s - %s)", 
                    $index + 1,
                    $interval['start']->format('d') . ' ' . TimeHelpers::getIndonesianMonthShort($interval['start']->format('n')),
                    $interval['end']->format('d') . ' ' . TimeHelpers::getIndonesianMonthShort($interval['end']->format('n'))
                ),
                default => TimeHelpers::HariInterval($interval['start'])
            };

            return [
                'label' => $label,
                'total' => $matchingData->sum('total')
            ];
        });
    }
}