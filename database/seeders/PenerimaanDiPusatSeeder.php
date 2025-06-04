<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PenerimaanDiPusat;

class PenerimaanDiPusatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PenerimaanDiPusat::create([
             'kode' => 'PDP-001',
             'id_jenis_penerimaan' => 1,
             'id_asal_barang' => 1,
             'id_barang' => 1,
             'jumlah_barang' => 100,
             'id_satuan_berat' => 1,
             'berat_satuan_barang' => 1000,
             'tanggal' => now()
         ]);
    }
}
