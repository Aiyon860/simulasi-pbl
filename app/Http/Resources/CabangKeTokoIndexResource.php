<?php

namespace App\Http\Resources;

use App\Helpers\TimeHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class CabangKeTokoIndexResource extends JsonResource
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
            'tujuan' => $this->toko->nama_gudang_toko,
            'nama_barang' => $this->barang->nama_barang,
            'id_status' => (int) $this->id_status,
            'status' => $this->status->nama_status,
            'jumlah_barang' => $this->jumlah_barang,
            'tanggal' => "{$day} {$month} {$tanggal->format('Y')}",
            'id_verifikasi' => (int) $this->verifikasi->id,
            'jenis_verifikasi' => $this->verifikasi->jenis_verifikasi,
        ];
    }
}