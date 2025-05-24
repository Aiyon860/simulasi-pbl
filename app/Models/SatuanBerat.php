<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SatuanBerat extends Model
{
    /** @use HasFactory<\Database\Factories\SatuanBeratFactory> */
        /** @use HasFactory<\Database\Factories\RoleFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama_satuan_berat',
    ];
    public function penerimaanDiCabang(): HasMany
    {
        return $this->hasMany(PenerimaanDiCabang::class, 'id_satuan_berat');
    }
    public function penerimaanDiPusat(): HasMany
    {
        return $this->hasMany(PenerimaanDiPusat::class, 'id_satuan_berat');
    }   

    public function pusatKeSupplier(): HasMany
    {
        return $this->hasMany(PusatKeSupplier::class, 'id_satuan_berat');
    }   
}
