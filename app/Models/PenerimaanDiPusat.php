<?php

namespace App\Models;

use App\Observers\PenerimaanDiPusatObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([PenerimaanDiPusatObserver::class])]
class PenerimaanDiPusat extends Model
{
    /** @use HasFactory<\Database\Factories\KurirFactory> */
    use HasFactory, SoftDeletes;
    /**
    * The attributes that are mass assignable.
    *
    * @var list<string>
    */
    protected $fillable = [
        'kode',
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
        'flag'
    ];

    protected $casts = [
        'tanggal' => 'datetime',
    ];

    public function jenisPenerimaan(): BelongsTo
    {
        return $this->belongsTo(JenisPenerimaan::class, 'id_jenis_penerimaan');
    }

    public function pusat(): BelongsTo
    {
        return $this->belongsTo(GudangDanToko::class, 'id_asal_barang');
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
        return $this->belongsTo(SupplierKePusat::class, 'id_laporan_pengiriman');
    }

    public function laporanRetur(): BelongsTo
    {
        return $this->belongsTo(CabangKePusat::class, 'id_laporan_retur');
    }
}
