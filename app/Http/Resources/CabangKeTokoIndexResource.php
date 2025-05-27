<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CabangKeTokoIndexResource extends JsonResource
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
            'kode' => $this->kode,
            'id_cabang' => $this->cabang->nama_gudang_toko,
            'id_toko' => $this->toko->nama_gudang_toko,
            'id_barang' => $this->barang->nama_barang,
            'id_kurir' => $this->kurir->nama_kurir,
            'id_satuan_berat' => $this->satuanBerat->nama_satuan_berat,
            'id_status' => $this->status->nama_status,
            'berat_satuan_barang' => (int) $this->berat_satuan_barang,
            'jumlah_barang' => (int) $this->jumlah_barang,
            'tanggal' => $this->tanggal, // Assuming tanggal is already formatted correctly
        ];
    }
}
