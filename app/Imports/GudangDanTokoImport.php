<?php

namespace App\Imports;

use App\Models\GudangDanToko;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GudangDanTokoImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new GudangDanToko([
            'nama_gudang_toko' => $row['nama_gudang_toko'],
            'kategori_bangunan' => $row['kategori_bangunan'],
            'alamat' => $row['alamat'],
            'no_telepon' => $row['no_telepon'],
        ]);
    }
}
