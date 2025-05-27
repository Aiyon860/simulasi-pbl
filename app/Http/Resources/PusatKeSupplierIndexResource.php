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
            'kode' => $this->kode,
            'id_barang' => $this->id_barang,
            'id_pusat' => $this->id_pusat,
            'id_supplier' => $this->id_supplier,
            'id_satuan_berat' => $this->id_satuan_berat,
            'id_kurir' => $this->id_kurir,
            'id_status' => $this->id_status,
            'berat_satuan_barang' => (int) $this->berat_satuan_barang,
            'jumlah_barang' => (int) $this->jumlah_barang,
            'tanggal' => "{$day} {$month} {$tanggal->format('Y')}",
        ];
    }
}
