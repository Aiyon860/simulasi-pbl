<?php

namespace App\Helpers;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class TimeHelpers
{
    /**
     * Mendapatkan array berisi jam dari 00.00 hingga jam sekarang
     *
     * @return array
     */
    public static function getHoursUntilNow(): array
    {
        // Mendapatkan jam saat ini dan tambah 1 jam
        $currentHour = (int)now()->format('H');
        $maxHour = min($currentHour + 1, 23);

        // Inisialisasi array untuk menyimpan jam
        $hours = [];

        // Loop dari jam 00 hingga jam sekarang + 1
        for ($i = 0; $i < $maxHour; $i++) {
            // Format jam dengan leading zero jika diperlukan
            $formattedHour = sprintf("%02d", $i);

            // Tambahkan jam ke array
            $hours[] = "{$formattedHour}.00";
        }

        return $hours;
    }

    /**
     * Mendapatkan 7 hari sebelumnya termasuk hari ini dengan format tanggal
     * dan 3 huruf awal nama bulan dalam bahasa Indonesia
     *
     * @return Collection
     */
    public static function getLastSevenDays(): Collection
    {
        $dates = collect();

        // Loop 7 hari terakhir (7 hari sebelumnya dari hari sebelum hari ini)
        for ($i = 7; $i > 0; $i--) {
            $date = now()->subDays($i);

            // Dapatkan tanggal dan 3 huruf awal bulan dalam bahasa Indonesia
            $day = $date->format('d');
            $monthShort = self::getIndonesianMonthShort($date->format('n'));

            $formattedDate = "{$day} {$monthShort}";

            $dates->push($formattedDate); // Format untuk display (20 Me);
        }

        return $dates;
    }

    /**
     * Mendapatkan 4 titik tanggal untuk hari ini dan 3 minggu sebelumnya pada hari yang sama
     * dengan format tanggal dan nama bulan dalam bahasa Indonesia
     *
     * @return Collection
     */
    public static function getFourDatesFromLastMonth(): Collection
    {
        $dates = collect();

        // Loop 4 minggu (4 minggu sebelumnya pada hari sebelum hari ini)
        for ($i = 4; $i > 0; $i--) {
            $date = now()->subWeeks($i);

            // Dapatkan tanggal dan nama bulan dalam bahasa Indonesia
            $day = $date->format('d');
            $monthShort = self::getIndonesianMonthShort($date->format('n'));

            $formattedDate = "{$day} {$monthShort}";

            $dates->push($formattedDate); // Format untuk display (20 Mei, 13 Mei, dst);
        }

        return $dates;
    }

    /**
     * Mendapatkan 3 huruf awal nama bulan dalam Bahasa Indonesia
     *
     * @param int $month Bulan (1-12)
     * @return string
     */
    public static function getIndonesianMonthShort(int $month): string
    {
        $months = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des'
        ];

        return $months[$month] ?? '';
    }

    public static function hariInterval($tanggal)
    {
        $date = Carbon::parse($tanggal);

        $day = $date->format('d');
        $month = self::getIndonesianMonthShort($date->format('n'));

        return "{$day} {$month}";
    }

    public static function getMingguanIntervals($jumlahInterval = 4)
    {
        $ranges = [];
        // Start from yesterday and work backwards
        $currentEnd = now()->subDay()->endOfDay();
        $daysPerWeek = 7;

        for ($i = 0; $i < $jumlahInterval; $i++) {
            $currentStart = $currentEnd->copy()->subDays($daysPerWeek - 1)->startOfDay();

            $startFormatted = $currentStart->format('d') . ' ' . self::getIndonesianMonthShort($currentStart->format('n'));
            $endFormatted = $currentEnd->format('d') . ' ' . self::getIndonesianMonthShort($currentEnd->format('n'));

            // Insert at the beginning of array to maintain chronological order
            array_unshift($ranges, [
                'label' => sprintf('Minggu ke-%d (%s - %s)', $i + 1, $startFormatted, $endFormatted),
                'start' => $currentStart->copy(),
                'end' => $currentEnd->copy(),
            ]);

            // Move to previous week
            $currentEnd = $currentStart->copy()->subDay()->endOfDay();
        }

        return $ranges;
    }

    public static function getHourlyIntervals(): array 
    {
        $currentHour = (int) now()->format('H');
        $intervals = [];
        
        for ($i = 0; $i <= $currentHour; $i++) {
            $start = sprintf("%02d:00", $i);
            $end = sprintf("%02d:00", $i + 1);
            $intervals[] = [
                'start' => Carbon::today()->setTimeFromTimeString($start),
                'end' => Carbon::today()->setTimeFromTimeString($end),
                'label' => "{$start} - {$end}"
            ];
        }
        
        return $intervals;
    }

    public static function getDailyIntervals(): array 
    {
        $intervals = [];
        $startDate = Carbon::yesterday()->subDays(6); // 7 days ago from yesterday
        $endDate = Carbon::yesterday(); // yesterday
        
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            $start = $date->copy();
            $end = $date->copy()->addDay();
            
            $intervals[] = [
                'start' => $start->startOfDay(),
                'end' => $start->copy()->endOfDay(),
                'label' => sprintf(
                    "%s - %s",
                    $start->format('d') . ' ' . self::getIndonesianMonthShort($start->format('n')),
                    $end->format('d') . ' ' . self::getIndonesianMonthShort($end->format('n'))
                )
            ];
        }
        
        return $intervals;
    }
}