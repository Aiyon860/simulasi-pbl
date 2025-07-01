<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PenerimaanDiPusatBarangCreateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'nama_barang' => $this->nama_barang,
            'id_satuan_berat' => (int) $this->id_satuan_berat,
            'nama_satuan_berat' => $this->satuanBerat->nama_satuan_berat,
            'berat_satuan_barang' => (int) $this->berat_satuan_barang,
        ];
    }
}
