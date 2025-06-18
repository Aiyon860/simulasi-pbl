<?php

namespace Database\Seeders;
use App\Models\Verifikasi;
use Illuminate\Database\Seeder;

use App\Imports\VerifikasiImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class VerifikasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Importing tipe verifikasi from Excel...');

        $disk = 'local';
        $fileName = 'Verifikasi.xlsx';
        $filePath = Storage::disk($disk)->path($fileName);

        Excel::import(new VerifikasiImport, $filePath);
    }
}
