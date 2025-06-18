<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TokoKeCabang extends Model
{
    /** @use HasFactory<\Database\Factories\TokoKeCabangFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *u
     * @var list<string>
     */
    protected $fillable = [
        'kode',
        'id_toko',
        'id_status',
        'id_kurir',
        'id_cabang',
        'id_barang',
        'id_satuan_berat',
        'id_verifikasi',
        'berat_satuan_barang',
        'jumlah_barang',
        'tanggal',
        'flag'
    ];

    public function toko(): BelongsTo
    {
        return $this->belongsTo(GudangDanToko::class,
        'id_toko');
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(GudangDanToko::class,
        'id_cabang');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class,
        'id_barang');
    }

    public function satuanBerat(): BelongsTo
    {
        return $this->belongsTo(SatuanBerat::class,
        'id_satuan_berat');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class,
        'id_status');
    }

    public function kurir(): BelongsTo
    {
        return $this->belongsTo(Kurir::class,
        'id_kurir');
    }

    public function verifikasi(): BelongsTo
    {
        return $this->belongsTo(Verifikasi::class, 'id_verifikasi');
    }
}
