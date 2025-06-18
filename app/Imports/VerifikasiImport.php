<?php

namespace App\Imports;

use App\Models\Verifikasi;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class VerifikasiImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Verifikasi([
            'jenis_verifikasi' => $row['jenis_verifikasi'],
        ]);
    }
}
