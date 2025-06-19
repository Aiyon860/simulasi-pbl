<?php

namespace App\Helpers;

use App\Models\TrackLog;
use Illuminate\Support\Facades\DB;

class TrackLogHelpers
{
    public static function createLog($idUser, $ipAddress, $logPesan) {
        DB::transaction(function () use ($idUser, $ipAddress, $logPesan) {
            TrackLog::create([
                'id_user' => $idUser,
                'ip_address' => $ipAddress,
                'aktivitas' => $logPesan,
                'tanggal_aktivitas' => now(),
            ]);
        }, 3);
    }
}