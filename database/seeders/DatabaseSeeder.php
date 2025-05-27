<?php

namespace Database\Seeders;

use App\Models\CabangKePusat;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            GudangDanTokoSeeder::class,
            UserSeeder::class,
            KategoriBarangSeeder::class,
            SatuanBeratSeeder::class,
            BarangSeeder::class,
            KurirSeeder::class,
            StatusSeeder::class,
            JenisPenerimaanSeeder::class,
            DetailGudangSeeder::class,
            // CabangKePusatSeeder::class,
            // TokoKeCabangSeeder::class,
            // CabangKeTokoSeeder::class,
            // PusatKeSupplierSeeder::class,
            PusatKeCabangSeeder::class,
            // PenerimaanDiPusatSeeder::class,
            PenerimaanDiCabangSeeder::class,
            // SupplierKePusatSeeder::class,
        ]);
    }
}
