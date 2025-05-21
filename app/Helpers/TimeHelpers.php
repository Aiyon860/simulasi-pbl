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
        for ($i = 0; $i <= $maxHour; $i++) {
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

    public static function getMingguanIntervals($start, $end, $jumlahInterval = 4)
    {
        $totalHari = $start->diffInDays($end) + 1; // +1 untuk inklusif
        $intervalHari = floor($totalHari / $jumlahInterval);
        $sisaHari = $totalHari % $jumlahInterval;

        $ranges = [];
        $currentStart = $start->copy()->startOfDay();

        for ($i = 0; $i < $jumlahInterval; $i++) {
            // Tambahkan sisa hari ke interval pertama
            $days = $intervalHari + ($i < $sisaHari ? 1 : 0);
            $currentEnd = $currentStart->copy()->addDays($days - 1)->endOfDay(); // -1 karena start dihitung juga

            if ($currentEnd->gt($end)) {
                $currentEnd = $end->copy()->endOfDay();
            }

            $ranges[] = [
                'label' => $currentStart->format('d M') . ' - ' . $currentEnd->format('d M'),
                'start' => $currentStart->copy(),
                'end' => $currentEnd->copy(),
            ];

            // Geser ke hari berikutnya, start of day
            $currentStart = $currentEnd->copy()->addDay()->startOfDay();
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

}
