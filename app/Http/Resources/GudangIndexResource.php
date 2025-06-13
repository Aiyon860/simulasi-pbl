<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use App\Helpers\TimeHelpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GudangIndexResource extends JsonResource
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
            'nama_gudang' => $this->nama_gudang_toko,
            'alamat' => $this->alamat,
            'no_telepon' => $this->no_telepon,
            'status' => $this->flag ? 'Aktif' : 'Nonaktif',
        ];
    }
}
