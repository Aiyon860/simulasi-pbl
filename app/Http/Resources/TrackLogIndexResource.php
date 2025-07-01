<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrackLogIndexResource extends JsonResource
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
            'nama_user' => $this->user->nama_user,
            'aktivitas' => $this->aktivitas,
            'tanggal_aktivitas' => $this->tanggal_aktivitas,
        ];
    }
}
