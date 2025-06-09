<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserShowResource extends JsonResource
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
            'nama_user' => $this->nama_user,
            'email' => $this->email,
            'role' => $this->role->nama_role,
            'id_lokasi' => $this->lokasi->id,
            'lokasi' => $this->lokasi->nama_gudang_toko,
            'status' => $this->flag ? 'Aktif' : 'Nonaktif',
        ];
    }
}
