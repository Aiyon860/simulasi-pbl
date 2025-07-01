<?php

namespace App\Observers;

use App\Helpers\TrackLogHelpers;
use App\Models\TrackLog;
use App\Models\CabangKeToko;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class CabangKeTokoObserver implements ShouldHandleEventsAfterCommit
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    
    /**
     * Handle the CabangKeToko "created" event.
     */
    public function created(CabangKeToko $cabangKeToko): void
    {
        $logPesan = "Melakukan pengiriman barang {$cabangKeToko->barang->nama_barang} sejumlah {$cabangKeToko->jumlah_barang} dengan kode {$cabangKeToko->kode} dari {$cabangKeToko->cabang->nama_gudang_toko} ke {$cabangKeToko->toko->nama_gudang_toko}";

        TrackLogHelpers::createLog(
            auth()->user()->id, 
            $this->request->ip(),
            $logPesan
        );
    }

    /**
     * Handle the CabangKeToko "updated" event.
     */
    public function updated(CabangKeToko $cabangKeToko): void
    {
        $logPesan = '';

        if ($cabangKeToko->wasChanged('id_status')) {
            $logPesan .= "Mengubah status laporan pengiriman dari {$cabangKeToko->cabang->nama_gudang_toko} ke {$cabangKeToko->toko->nama_gudang_toko} dengan kode {$cabangKeToko->kode} menjadi '{$cabangKeToko->status->nama_status}'";
        } else if ($cabangKeToko->wasChanged('id_verifikasi')) {
            $jenis_verifikasi = $cabangKeToko->verifikasi->jenis_verifikasi;

            $logPesan .= match ($jenis_verifikasi) {
                'Terverifikasi' => "Memverifikasi laporan pengiriman dari {$cabangKeToko->cabang->nama_gudang_toko} ke {$cabangKeToko->toko->nama_gudang_toko} dengan kode {$cabangKeToko->kode}",
                'Tidak Diverifikasi' => "Tidak memverifikasi laporan pengiriman dari {$cabangKeToko->cabang->nama_gudang_toko} ke {$cabangKeToko->toko->nama_gudang_toko} dengan kode {$cabangKeToko->kode}",
            };
        } else if ($cabangKeToko->wasChanged('flag')) {
            $logPesan .= "Menghapus laporan pengiriman dari {$cabangKeToko->cabang->nama_gudang_toko} ke {$cabangKeToko->toko->nama_gudang_toko} dengan kode {$cabangKeToko->kode}";
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
