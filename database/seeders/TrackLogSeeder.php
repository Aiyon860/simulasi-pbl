<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TrackLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TrackLogSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first(); // ambil user pertama sebagai contoh

        if ($user) {
            TrackLog::create([
                'user_id' => $user->id,
                'ip_address' => '192.168.1.1',
                'aktivitas' => 'pengiriman',
                'tanggal_aksi' => now(),
            ]);
        }
    }
}

