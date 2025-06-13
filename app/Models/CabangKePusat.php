<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
class CabangKePusat extends Model
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
        'id_pusat',
        'id_status',
        'id_kurir',
        'id_cabang',
        'id_barang',
        'id_satuan_berat',
        'berat_satuan_barang',
        'jumlah_barang',
        'tanggal',
        'flag',
    ];

    public function pusat() : BelongsTo
    {
        return $this->belongsTo(GudangDanToko::class, 'id_pusat');
    }

    public function kurir()
    {
        return $this->belongsTo(Kurir::class, 'id_kurir');
    }
    public function status()
    {
        return $this->belongsTo(Status::class, 'id_status');
    }

    public function cabang() : BelongsTo
    {
        return $this->belongsTo(GudangDanToko::class, 'id_cabang');
    }

    public function barang() : BelongsTo
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }
    public function satuanBerat(): BelongsTo
    {
        return $this->belongsTo(SatuanBerat::class, 'id_satuan_berat');
    }
}
