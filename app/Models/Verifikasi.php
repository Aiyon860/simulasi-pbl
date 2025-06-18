<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Verifikasi extends Model
{
    use HasFactory;

    protected $table = 'verifikasi';

    protected $fillable = [
        'jenis_verifikasi'
    ];

    public function CabangKePusat(): HasMany
    {
        return $this->HasMany(CabangKePusat::class, 'id_verifikasi');
    }
    public function CabangKeToko(): HasMany
    {
        return $this->HasMany(CabangKeToko::class, 'id_verifikasi');
    }
    public function PenerimaanDiCabang(): HasMany
    {
        return $this->HasMany(PenerimaanDiCabang::class, 'id_verifikasi');
    }
    public function PenerimaanDiPusat(): HasMany
    {
        return $this->HasMany(PenerimaanDiPusat::class, 'id_verifikasi');
    }
    public function PusatKeCabang(): HasMany
    {
        return $this->HasMany(PusatKeCabang::class, 'id_verifikasi');
    }
    public function PusatKeSupplier(): HasMany
    {
        return $this->HasMany(PusatKeSupplier::class, 'id_verifikasi');
    }
    public function SupplierKePusat(): HasMany
    {
        return $this->HasMany(SupplierKePusat::class, 'id_verifikasi');
    }
    public function TokoKeCabang(): HasMany
    {
        return $this->HasMany(TokoKeCabang::class, 'id_verifikasi');
    }
}

