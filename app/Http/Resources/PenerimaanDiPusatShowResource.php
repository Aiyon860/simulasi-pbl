<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use App\Helpers\TimeHelpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PenerimaanDiPusatShowResource extends JsonResource
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
            'jenis_penerimaan' => $this->jenisPenerimaan->nama_jenis_penerimaan,
            'nama_pusat' => $this->pusat->nama_gudang_toko,
            'asal_barang' => $this->asalBarang->nama_gudang_toko,
            'nama_barang' => $this->barang->nama_barang,
            'satuan_berat' => $this->satuanBerat->nama_satuan_berat,
            'berat_satuan_barang' => (int) $this->berat_satuan_barang,
            'jumlah_barang' => (int) $this->jumlah_barang,
            'tanggal' => "{$day} {$month} {$tanggal->format('Y')}",
            'diterima' => (int) $this->diterima,
            'kode_resi' => $this->laporanPengiriman->kode ?? $this->laporanRetur->kode ?? null,
        ];
    }
}