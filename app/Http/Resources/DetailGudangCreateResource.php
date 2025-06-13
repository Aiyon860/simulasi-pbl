<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailGudangCreateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, // id barang dari gudang admin, bukan id barang in general
            'nama_barang' => $this->barang->nama_barang,
            'nama_satuan_berat' => $this->satuanBerat->nama_satuan_berat
        ];
    }
}
