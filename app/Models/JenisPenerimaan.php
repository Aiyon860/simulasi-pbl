<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
class JenisPenerimaan extends Model
{
    /** @use HasFactory<\Database\Factories\KurirFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama_jenis_penerimaan'
    ];

    public function penerimaanDiCabang()
    {
        return $this->hasMany(PenerimaanDiCabang::class, 'id_jenis_penerimaan');
    }
    public function penerimaanDiPusat()
    {
        return $this->hasMany(PusatKeSupplier::class, 'id_jenis_penerimaan');
    }
}
