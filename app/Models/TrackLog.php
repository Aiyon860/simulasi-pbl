<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrackLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip_address',
        'aktivitas',
        'tanggal_aksi',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

