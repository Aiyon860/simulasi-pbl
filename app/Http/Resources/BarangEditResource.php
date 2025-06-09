<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BarangEditResource extends JsonResource
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
            'id_kategori_barang' => $this->kategori->id,
            'kategori_barang' => $this->kategori->nama_kategori_barang,
        ];
    }
}
