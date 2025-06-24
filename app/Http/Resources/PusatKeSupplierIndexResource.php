<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use App\Helpers\TimeHelpers;
class PusatKeSupplierIndexResource extends JsonResource
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

        return[
            'id' => (int) $this->id,
            'nama_barang' => $this->barang->nama_barang,
            'tujuan' => $this->supplier->nama_gudang_toko,
            'id_status' => (int) $this->id_status,
            'status' => $this->status->nama_status,
            'jumlah_barang' => (int) $this->jumlah_barang,
            'tanggal' => "{$day} {$month} {$tanggal->format('Y')}",
            'id_verifikasi' => (int) $this->verifikasi->id,
            'jenis_verifikasi' => $this->verifikasi->jenis_verifikasi,
        ];
    }
}