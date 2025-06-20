<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SupplierKePusat extends Model
{
    /** @use HasFactory<\Database\Factories\SupplierKePusatFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'kode',
        'id_supplier',
        'id_pusat',
        'id_status',
        'id_kurir',
        'id_barang',
        'id_verifikasi',
        'jumlah_barang',
        'berat_satuan_barang',
        'id_satuan_berat',
        'tanggal',
        'flag'
    ];
    public function supplier(): BelongsTo 
    {
        return $this->belongsTo(GudangDanToko::Class,'id_supplier');
    }

    public function pusat(): BelongsTo 
    {
        return $this->belongsTo(GudangDanToko::Class, 'id_pusat');
    }

    public function barang(): BelongsTo 
    {
        return $this->belongsTo(Barang::Class,'id_barang');
    }


    public function kurir()
    {
        return $this->belongsTo(Kurir::class, 'id_kurir');
    }
    public function status()
    {
        return $this->belongsTo(Status::class, 'id_status');
    }

    public function satuanBerat(): BelongsTo 
    {
        return $this->belongsTo(SatuanBerat::class, 'id_satuan_berat');
    }

    public function verifikasi(): BelongsTo
    {
        return $this->belongsTo(Verifikasi::class, 'id_verifikasi');
    }

    public function laporanPengiriman(): HasOne
    {
        return $this->hasOne(PenerimaanDiPusat::class, 'id_laporan_pengiriman');
    }
}
