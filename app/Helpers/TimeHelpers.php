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
        // Mendapatkan jam saat ini
        $currentHour = (int)now()->format('H');

        // Inisialisasi array untuk menyimpan jam
        $hours = [];

        // Loop dari jam 00 hingga jam sekarang
        for ($i = 0; $i <= $currentHour; $i++) {
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
    private static function getIndonesianMonthShort(int $month): string
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

    public static function jamInterval($jamGrup)
    {
        $start = Carbon::parse($jamGrup);
        $end = $start->copy()->addHour();
        return $start->format('H:i') . ' - ' . $end->format('H:i');
    }

    public static function MingguInterval($tanggal)
{
    $start = Carbon::parse($tanggal);
    $end = $start->copy()->addDays(7);

    $startFormatted = $start->format('d') . ' ' . self::getIndonesianMonthShort($start->format('n'));
    $endFormatted = $end->format('d') . ' ' . self::getIndonesianMonthShort($end->format('n'));

    return "{$startFormatted} - {$endFormatted}";
}

public static function getMingguanIntervals($start, $end, $jumlahInterval = 4)
    {
        $totalHari = $start->diffInDays($end);
        $intervalHari = ceil($totalHari / $jumlahInterval);

        $ranges = [];
        $currentStart = $start->copy();

        for ($i = 0; $i < $jumlahInterval; $i++) {
            $currentEnd = $currentStart->copy()->addDays($intervalHari);
            if ($currentEnd->greaterThan($end)) {
                $currentEnd = $end->copy();
            }

            $ranges[] = [
                'label' => $currentStart->format('d M') . ' - ' . $currentEnd->format('d M'),
                'start' => $currentStart->copy()->startOfDay(),
                'end' => $currentEnd->copy()->endOfDay(),
            ];

            $currentStart = $currentEnd->copy();
        }

        return $ranges;
    }

    public static function hariInterval($tanggal)
    {
        $start = Carbon::parse($tanggal);
        $end = $start->copy()->addDay();

        $startDay = $start->format('d');
        $startMonth = self::getIndonesianMonthShort($start->format('n'));

        $endDay = $end->format('d');
        $endMonth = self::getIndonesianMonthShort($end->format('n'));

        return "{$startDay} {$startMonth} - {$endDay} {$endMonth}";
    }
}
