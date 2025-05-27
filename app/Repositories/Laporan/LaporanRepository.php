<?php

namespace App\Repositories\Laporan;

use Illuminate\Support\Facades\DB;

class LaporanRepository
{
    // Base query for laporan
    protected function getBaseLaporanQuery($model, $namaGrup, $format, $jenisPenerimaan = null, $useJenisPenerimaan = true)
    {
        $query = $model::select(
            DB::raw("DATE_FORMAT(tanggal, '{$format}') as {$namaGrup}"),
            DB::raw("COUNT(*) as total")
        );

        if ($useJenisPenerimaan && $jenisPenerimaan) {
            $query->whereHas('jenisPenerimaan', function ($query) use ($jenisPenerimaan) {
                $query->where('nama_jenis_penerimaan', $jenisPenerimaan);
            });
        }

        return $query;
    }
}