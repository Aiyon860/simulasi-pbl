<?php

namespace Database\Seeders;

use App\Imports\KurirImport;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class KurirSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Importing kurir from Excel...');

        $disk = 'local';
        $fileName = 'Kurir.xlsx';
        $filePath = Storage::disk($disk)->path($fileName);

        Excel::import(new KurirImport, $filePath);
    }
}
