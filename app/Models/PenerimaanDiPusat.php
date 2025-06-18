<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'id_jenis_penerimaan',
        'id_asal_barang',
        'id_barang',
        'id_satuan_berat',
        'id_verifikasi',
        'berat_satuan_barang',
        'jumlah_barang',
        'tanggal',
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
}
