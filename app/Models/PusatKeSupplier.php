<?php

namespace App\Models;
use App\Observers\PusatKeSupplierObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([PusatKeSupplierObserver::class])]
class PusatKeSupplier extends Model
{
    use HasFactory,SoftDeletes;
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
        'id_supplier',
        'id_barang',
        'id_satuan_berat',
        'id_verifikasi',
        'berat_satuan_barang',
        'jumlah_barang',
        'tanggal',
        'flag'
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }
    public function pusat()
    {
        return $this->belongsTo(GudangDanToko::class, 'id_pusat');
    }
    public function supplier()
    {
        return $this->belongsTo(GudangDanToko::class, 'id_supplier');
    }
    public function satuanBerat()
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

    public function verifikasi(): BelongsTo
    {
        return $this->belongsTo(Verifikasi::class, 'id_verifikasi');
    }
}
