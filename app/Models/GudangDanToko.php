<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GudangDanToko extends Model
{
    /** @use HasFactory<\Database\Factories\GudangFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama_gudang_toko',
        'kategori_bangunan',
        'alamat',
        'no_telepon',
        'flag'
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'id_gudang');
    }

    public function supplierKeTujuanPusat(): HasMany
    {
        return $this->hasMany(SupplierKePusat::class, 'id_pusat');
    }

    public function asalSupplierKePusat(): HasMany
    {
        return $this->hasMany(SupplierKePusat::class, 'id_supplier');
    }

    public function pusatKeTujuanCabang(): HasMany
    {
        return $this->hasMany(PusatKeCabang::class, 'id_cabang');
    }

    public function asalPusatKeCabang(): HasMany
    {
        return $this->hasMany(PusatKeCabang::class, 'id_pusat');
    }

    public function cabangKeTujuanToko(): HasMany
    {
        return $this->hasMany(CabangKeToko::class, 'id_toko');
    }

    public function asalCabangKeToko(): HasMany
    {
        return $this->hasMany(CabangKeToko::class, 'id_cabang');
    }

    public function asalPenerimaanDiPusat(): HasMany
    {
        return $this->hasMany(PenerimaanDiPusat::class, 'id_asal_barang');
    }

    public function asalPenerimaanDiCabang(): HasMany
    {
        return $this->hasMany(PenerimaanDiPusat::class, 'id_asal_barang');
    }
}
