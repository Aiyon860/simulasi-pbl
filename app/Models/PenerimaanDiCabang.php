<?php

namespace App\Models;

use App\Observers\PenerimaanDiCabangObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ObservedBy([PenerimaanDiCabangObserver::class])]
class PenerimaanDiCabang extends Model
{
    /** @use HasFactory<\Database\Factories\PenerimaanDiCabangFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'kode',
        'id_cabang',
        'id_jenis_penerimaan',
        'id_asal_barang',
        'id_barang',
        'id_satuan_berat',
        'id_verifikasi',
        'id_laporan_pengiriman', // nullable
        'id_laporan_retur',      // nullable
        'berat_satuan_barang',
        'jumlah_barang',
        'tanggal',
        'diterima',
        'flag',
    ];

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(GudangDanToko::class, 'id_cabang');
    }

    public function jenisPenerimaan(): BelongsTo
    {
        return $this->belongsTo(JenisPenerimaan::class, 'id_jenis_penerimaan');
    }

    public function asalBarang(): BelongsTo
    {
        return $this->belongsTo(GudangDanToko::class, 'id_asal_barang');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }

    public function satuanBerat(): BelongsTo
    {
        return $this->belongsTo(SatuanBerat::class, 'id_satuan_berat');
    }

    public function verifikasi(): BelongsTo
    {
        return $this->belongsTo(Verifikasi::class, 'id_verifikasi');
    }

    public function laporanPengiriman(): BelongsTo
    {
        return $this->belongsTo(PusatKeCabang::class, 'id_laporan_pengiriman');
    }

    public function laporanRetur(): BelongsTo
    {
        return $this->belongsTo(TokoKeCabang::class, 'id_laporan_retur');
    }
}
