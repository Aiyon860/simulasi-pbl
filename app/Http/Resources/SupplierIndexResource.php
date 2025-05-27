<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return[
            'nama_gudang_toko' => $this->nama_gudang_toko,
            'alamat' => $this->alamat,
            'no_telepon' => $this->no_telepon,
            'flag' => $this->flag,
        ];
    }
}
