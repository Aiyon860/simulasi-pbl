<?php

namespace Database\Seeders;
use App\Models\Verifikasi;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VerifikasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['jenis_verifikasi' => 'Belum diverifikasi'],
            ['jenis_verifikasi' => 'Sudah diverifikasi'],
            ['jenis_verifikasi' => 'Tidak diverifikasi'],
        ];

        foreach ($data as $item) {
            Verifikasi::create($item);
        }
    }
}
