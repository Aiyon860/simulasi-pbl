<?php

namespace App\Observers;

use App\Helpers\TrackLogHelpers;
use App\Models\TrackLog;
use Illuminate\Http\Request;
use App\Models\CabangKePusat;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class CabangKePusatObserver implements ShouldHandleEventsAfterCommit
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the CabangKePusat "created" event.
     */
    public function created(CabangKePusat $cabangKePusat): void
    {
        $logPesan = "Melakukan retur barang {$cabangKePusat->barang->nama_barang} sejumlah {$cabangKePusat->jumlah_barang} dengan kode {$cabangKePusat->kode} dari {$cabangKePusat->cabang->nama_gudang_toko} ke Gudang Pusat";

        TrackLogHelpers::createLog(
            auth()->user()->id, 
            $this->request->ip(),
            $logPesan
        );
    }

    /**
     * Handle the CabangKePusat "updated" event.
     */
    public function updated(CabangKePusat $cabangKePusat): void
    {
        $logPesan = '';

        if ($cabangKePusat->wasChanged('id_status')) {
            $logPesan .= "Mengubah status laporan retur dari {$cabangKePusat->cabang->nama_gudang_toko} ke Gudang Pusat dengan kode {$cabangKePusat->kode} menjadi '{$cabangKePusat->status->nama_status}'";
        } else if ($cabangKePusat->wasChanged('id_verifikasi')) {
            $jenis_verifikasi = $cabangKePusat->verifikasi->jenis_verifikasi;

            $logPesan .= match ($jenis_verifikasi) {
                'Terverifikasi' => "Memverifikasi laporan retur dari {$cabangKePusat->cabang->nama_gudang_toko} ke Gudang Pusat dengan kode {$cabangKePusat->kode}",
                'Tidak Diverifikasi' => "Tidak memverifikasi laporan retur dari {$cabangKePusat->cabang->nama_gudang_toko} ke Gudang Pusat dengan kode {$cabangKePusat->kode}",
            };
        } else if ($cabangKePusat->wasChanged('flag')) {
            $logPesan .= "Menghapus laporan retur dari {$cabangKePusat->cabang->nama_gudang_toko} ke Gudang Pusat dengan kode {$cabangKePusat->kode}";
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
