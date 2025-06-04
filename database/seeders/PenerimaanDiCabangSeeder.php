<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PenerimaanDiCabang;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PenerimaanDiCabangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PenerimaanDiCabang::create([
            'kode' => 'PDC-001',
            'id_cabang' => 2,
            'id_jenis_penerimaan' => 1,
            'id_asal_barang' => 1,
            'id_barang' => 1,
            'id_satuan_berat' => 1,
            'berat_satuan_barang' => 10,
            'jumlah_barang' => 10,
            'tanggal' => now()
        ]);
    }
}
