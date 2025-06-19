<?php

namespace App\Observers;

use Illuminate\Http\Request;
use App\Helpers\TrackLogHelpers;
use App\Models\PenerimaanDiPusat;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class PenerimaanDiPusatObserver implements ShouldHandleEventsAfterCommit
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    
    /**
     * Handle the PenerimaanDiPusat "created" event.
     */
    public function created(PenerimaanDiPusat $penerimaanDiPusat): void
    {
        $logPesan = "Melakukan penerimaan di pusat dengan barang {$penerimaanDiPusat->barang->nama_barang} sejumlah {$penerimaanDiPusat->jumlah_barang} dengan jenis penerimaan berupa {$penerimaanDiPusat->jenisPenerimaan->nama_jenis_penerimaan} dari {$penerimaanDiPusat->asalBarang->nama_gudang_toko}";

        TrackLogHelpers::createLog(
            auth()->user()->id, 
            $this->request->ip(),
            $logPesan
        );
    }

    /**
     * Handle the PenerimaanDiPusat "updated" event.
     */
    public function updated(PenerimaanDiPusat $penerimaanDiPusat): void
    {
        $logPesan = '';
        
        if ($penerimaanDiPusat->wasChanged('id_verifikasi')) {
            $jenis_verifikasi = $penerimaanDiPusat->verifikasi->jenis_verifikasi;

            $logPesan .= match ($jenis_verifikasi) {
                'Terverifikasi' => "Memverifikasi salah satu laporan penerimaan barang di pusat dari {$penerimaanDiPusat->asalBarang->nama_gudang_toko}",
                'Tidak Diverifikasi' => "Tidak memverifikasi salah satu laporan penerimaan barang di pusat dari {$penerimaanDiPusat->asalBarang->nama_gudang_toko}",
            };
        } else if ($penerimaanDiPusat->wasChanged('flag')) {
            $logPesan .= "Menghapus salah satu laporan penerimaan barang di pusat dari {$penerimaanDiPusat->asalBarang->nama_gudang_toko}";
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
