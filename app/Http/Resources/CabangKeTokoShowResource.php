<?php

namespace App\Http\Resources;

use App\Helpers\TimeHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class CabangKeTokoShowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $tanggal = Carbon::parse($this->tanggal);
        $day = $tanggal->format('d');
        $month = TimeHelpers::getIndonesianMonthShort($tanggal->format('n'));

        return [
            'id' => (int) $this->id,
            'kode' => $this->kode,
            'nama_cabang' => $this->cabang->nama_gudang_toko,
            'nama_toko' => $this->toko->nama_gudang_toko,
            'nama_barang' => $this->barang->nama_barang,
            'nama_kurir' => $this->kurir->nama_kurir,
            'satuan_berat' => $this->satuanBerat->nama_satuan_berat,
            'id_status' => (int) $this->id_status,
            'status' => $this->status->nama_status,
            'berat_satuan_barang' => $this->berat_satuan_barang,
            'jumlah_barang' => $this->jumlah_barang,
            'tanggal' => "{$day} {$month} {$tanggal->format('Y')}",
            'jenis_verifikasi' => $this->verifikasi->jenis_verifikasi,
        ];
    }
}