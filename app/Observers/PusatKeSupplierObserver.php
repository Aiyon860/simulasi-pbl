<?php

namespace App\Observers;

use App\Helpers\TrackLogHelpers;
use App\Models\TrackLog;
use App\Models\PusatKeSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class PusatKeSupplierObserver implements ShouldHandleEventsAfterCommit
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the PusatKeSupplier "created" event.
     */
    public function created(PusatKeSupplier $pusatKeSupplier): void
    {
        $logPesan = "Melakukan retur barang {$pusatKeSupplier->barang->nama_barang} sejumlah {$pusatKeSupplier->jumlah_barang} dengan kode {$pusatKeSupplier->kode} dari Gudang Pusat ke {$pusatKeSupplier->supplier->nama_gudang_toko}";

        TrackLogHelpers::createLog(
            auth()->user()->id, 
            $this->request->ip(),
            $logPesan
        );
    }

    /**
     * Handle the PusatKeSupplier "updated" event.
     */
    public function updated(PusatKeSupplier $pusatKeSupplier): void
    {
        $logPesan = '';

        if ($pusatKeSupplier->wasChanged('id_status')) {
            $logPesan .= "Mengubah status laporan retur dari Gudang Pusat ke {$pusatKeSupplier->supplier->nama_gudang_toko} dengan kode {$pusatKeSupplier->kode} menjadi '{$pusatKeSupplier->status->nama_status}'";
        } else if ($pusatKeSupplier->wasChanged('id_verifikasi')) {
            $jenis_verifikasi = $pusatKeSupplier->verifikasi->jenis_verifikasi;

            $logPesan .= match ($jenis_verifikasi) {
                'Terverifikasi' => "Memverifikasi laporan retur dari Gudang Pusat ke {$pusatKeSupplier->supplier->nama_gudang_toko} dengan kode {$pusatKeSupplier->kode}",
                'Tidak Diverifikasi' => "Tidak memverifikasi laporan retur dari Gudang Pusat ke {$pusatKeSupplier->supplier->nama_gudang_toko} dengan kode {$pusatKeSupplier->kode}",
            };
        } else if ($pusatKeSupplier->wasChanged('flag')) {
            $logPesan .= "Menghapus laporan retur dari Gudang Pusat ke {$pusatKeSupplier->supplier->nama_gudang_toko} dengan kode {$pusatKeSupplier->kode}";
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
