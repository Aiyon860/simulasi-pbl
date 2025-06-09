<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AsalBarangCreateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama_gudang' => $this->nama_gudang_toko,
            'kategori_bangunan' => $this->kategori_bangunan,
            'tipe_asal' => $this->kategori_bangunan === 0 ? 'cabang' : 'supplier',
            'tipe_asal_cabang' => $this->kategori_bangunan === 2 ? 'toko' : 'pusat',
        ];
    }
}
