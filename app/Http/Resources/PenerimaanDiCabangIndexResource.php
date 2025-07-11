<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use App\Helpers\TimeHelpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PenerimaanDiCabangIndexResource extends JsonResource
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
            'nama_barang' => $this->barang->nama_barang,
            'jenis_penerimaan' => $this->jenisPenerimaan->nama_jenis_penerimaan,
            'asal_barang' => $this->asalBarang->nama_gudang_toko,
            'jumlah_barang' => (int) $this->jumlah_barang,
            'tanggal' => "{$day} {$month} {$tanggal->format('Y')}",
            'diterima' => (int) $this->diterima,
            'id_verifikasi' => (int) $this->verifikasi->id,
            'jenis_verifikasi' => $this->verifikasi->jenis_verifikasi,
        ];
    }
}
