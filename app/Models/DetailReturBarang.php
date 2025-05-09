<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailReturBarang extends Model
{
    /** @use HasFactory<\Database\Factories\DetailReturBarangFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_retur',
        'id_pengiriman_barang',
        'id_barang',
        'jumlah_barang_retur',
    ];

    public function returBarang()
    {
        return $this->belongsTo(ReturBarang::class, 'id_retur');
    }
    
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }
}
