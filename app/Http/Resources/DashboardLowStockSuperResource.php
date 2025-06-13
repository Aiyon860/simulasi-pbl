<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardLowStockSuperResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'nama_barang' => $this->barang->nama_barang,
            'nama_gudang' => $this->gudang->nama_gudang_toko,
            'jumlah_stok' => (int) $this->jumlah_stok,
        ];
    }
}
