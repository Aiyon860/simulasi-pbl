<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailGudangIndexResource extends JsonResource
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
            'id_barang' => (int) $this->id_barang,
            'nama_barang' => $this->barang->nama_barang,
            'id_gudang' => (int) $this->id_gudang,
            'nama_gudang' => $this->gudang->nama_gudang_toko,
            'id_satuan_berat' => (int) $this->id_satuan_berat,
            'nama_satuan_berat' => $this->satuanBerat->nama_satuan_berat,
            'jumlah_stok' => (int) $this->jumlah_stok,
            'stok_opname' => (int) $this->stok_opname,
            'status' => $this->flag ? 'Aktif' : 'Nonaktif',
        ];
    }
}
