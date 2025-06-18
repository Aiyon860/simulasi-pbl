<?php

namespace App\Imports;

use App\Models\Barang;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BarangImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        echo json_encode($row);

        return new Barang([
            'nama_barang' => $row['nama_barang'],
            'id_kategori_barang' => $row['id_kategori_barang'],
            'id_satuan_berat' => $row['id_satuan_berat'],
        ]);
    }
}
