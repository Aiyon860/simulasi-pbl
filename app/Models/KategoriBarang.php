<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriBarang extends Model
{
    /** @use HasFactory<\Database\Factories\KategoriBarangFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama_kategori_barang',
        'flag'
    ];

    public function barangs(): HasMany
    {
        return $this->hasMany(Barang::class, 'id_kategori_barang');
    }
}
