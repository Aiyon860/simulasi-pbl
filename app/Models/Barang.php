<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Barang extends Model
{
    /** @use HasFactory<\Database\Factories\BarangFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama_barang',
        'id_kategori_barang',
        'flag'
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriBarang::class, 'id_kategori_barang');
    }

    // public function childBarang(): HasMany
    // {
    //     return $this->hasMany(DetailGudang::class, 'id_barang');
    // }
    
    // public function gudang(): BelongsTo
    // {
    //     return $this->belongsTo(GudangDanToko::class, 'id_gudang');
    // }

    // public function supplierKePusat(): HasMany
    // {
    //     return $this->belongsTo(SupplierKePusat::class, 'id_barang');
    // }

    // public function pusatKeCabang(): HasMany
    // {
    //     return $this->belongsTo(PusatKeCabang::class, 'id_barang');
    // }

    // public function cabangKeToko(): HasMany
    // {
    //     return $this->belongsTo(CabangKeToko::class, 'id_barang');
    // }

    // public function tokoKeCabang(): HasMany
    // {
    //     return $this->belongsTo(TokoKeCabang::class, 'id_barang');
    // }

    // public function cabangKePusat(): HasMany
    // {
    //     return $this->belongsTo(cabangKePusat::class, 'id_barang');
    // }
    
    // public function pusatKeSupplier(): HasMany
    // {
    //     return $this->belongsTo(PusatKeSupplier::class, 'id_barang');
    // }

    public function penerimaanDiPusat(): HasMany
    {
        return $this->hasMany(PenerimaanDiPusat::class, 'id_barang');
    }

    public function penerimaanDiCabang(): HasMany
    {
        return $this->hasMany(PenerimaanDiCabang::class, 'id_barang');
    }

    // public function detailPengirimanBarang(): HasMany
    // {
    //     return $this->hasMany(DetailPenerimaanBarang::class, 'id_barang');
    // }
}
