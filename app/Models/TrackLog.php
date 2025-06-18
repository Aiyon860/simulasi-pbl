<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_user',
        'ip_address',
        'aktivitas',
        'tanggal_aktivitas',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}

