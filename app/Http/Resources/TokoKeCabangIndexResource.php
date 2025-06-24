<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use App\Helpers\TimeHelpers;

class TokoKeCabangIndexResource extends JsonResource
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
            'berat_satuan_barang' => (int) $this->berat_satuan_barang,
            'jumlah_barang' => (int) $this->jumlah_barang,
            'tanggal' => "{$day} {$month} {$tanggal->format('Y')}",
            'id_verifikasi' => (int) $this->verifikasi->id,
            'jenis_verifikasi' => $this->verifikasi->jenis_verifikasi,
        ];
    }
}
