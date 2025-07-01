<?php

namespace App\Observers;

use Illuminate\Http\Request;
use App\Models\GudangDanToko;
use App\Helpers\TrackLogHelpers;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class GudangDanTokoObserver implements ShouldHandleEventsAfterCommit
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the GudangDanToko "updated" event.
     */
    public function updated(GudangDanToko $gudangDanToko): void
    {
        $logPesan = '';
        
        if ($gudangDanToko->wasChanged('flag')) {
            $logPesan .= match ($gudangDanToko->flag) {
                0 => "Mengubah status opname {$gudangDanToko->nama_gudang_toko} menjadi aktif",
                1 => "Mengubah status opname {$gudangDanToko->nama_gudang_toko} menjadi tidak aktif",
            };
        }

        if (!empty($logPesan)) {
            TrackLogHelpers::createLog(
                auth()->user()->id,
                $this->request->ip(),
                $logPesan
            );
        }
    }
}
