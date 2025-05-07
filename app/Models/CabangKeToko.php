<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CabangKeToko extends Model
{
    /** @use HasFactory<\Database\Factories\CabangKeTokoFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'kode',
        'id_status',
        'id_kurir',
        'id_cabang',
        'id_toko',
        'id_barang',
        'id_satuan_berat',
        'berat_satuan_barang',
        'jumlah_barang',
        'tanggal',
        'flag',
    ];

    public function cabang()
    {
        return $this->belongsTo(GudangDanToko::class, 'id_cabang');
    }

    public function toko()
    {
        return $this->belongsTo(GudangDanToko::class, 'id_toko');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }

    public function satuanBerat(): BelongsTo
    {
        return $this->belongsTo(SatuanBerat::class, 'id_satuan_berat');
    }

    public function kurir()
    {
        return $this->belongsTo(Kurir::class, 'id_kurir');
    }
    public function status()
    {
        return $this->belongsTo(Status::class, 'id_status');
    }
}
