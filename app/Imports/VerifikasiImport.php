<?php

namespace App\Imports;

use App\Models\Verifikasi;
use Maatwebsite\Excel\Concerns\ToModel;

class VerifikasiImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Verifikasi([
            'jenis_verifikasi' => $row['jenis verifikasi'],
        ]);
    }
}
