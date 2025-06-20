<?php

namespace App\Observers;

use App\Helpers\TrackLogHelpers;
use App\Models\TrackLog;
use App\Models\PusatKeCabang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class PusatKeCabangObserver implements ShouldHandleEventsAfterCommit
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the PusatKeCabang "created" event.
     */
    public function created(PusatKeCabang $pusatKeCabang): void
    {
        $logPesan = "Melakukan pengiriman barang {$pusatKeCabang->barang->nama_barang} sejumlah {$pusatKeCabang->jumlah_barang} dengan kode {$pusatKeCabang->kode} dari Gudang Pusat ke {$pusatKeCabang->cabang->nama_gudang_toko}";

        TrackLogHelpers::createLog(
            auth()->user()->id, 
            $this->request->ip(),
            $logPesan
        );
    }

    /**
     * Handle the PusatKeCabang "updated" event.
     */
    public function updated(PusatKeCabang $pusatKeCabang): void
    {
        $logPesan = '';

        if ($pusatKeCabang->wasChanged('id_status')) {
            $logPesan .= "Mengubah status laporan pengiriman dari Gudang Pusat ke {$pusatKeCabang->cabang->nama_gudang_toko} dengan kode {$pusatKeCabang->kode} menjadi '{$pusatKeCabang->status->nama_status}'";
        } else if ($pusatKeCabang->wasChanged('id_verifikasi')) {
            $jenis_verifikasi = $pusatKeCabang->verifikasi->jenis_verifikasi;

            $logPesan .= match ($jenis_verifikasi) {
                'Terverifikasi' => "Memverifikasi laporan pengiriman dari Gudang Pusat ke {$pusatKeCabang->cabang->nama_gudang_toko} dengan kode {$pusatKeCabang->kode}",
                'Tidak Diverifikasi' => "Tidak memverifikasi laporan pengiriman dari Gudang Pusat ke {$pusatKeCabang->cabang->nama_gudang_toko} dengan kode {$pusatKeCabang->kode}",
            };
        } else if ($pusatKeCabang->wasChanged('flag')) {
            $logPesan .= "Menghapus laporan pengiriman dari Gudang Pusat ke {$pusatKeCabang->cabang->nama_gudang_toko} dengan kode {$pusatKeCabang->kode}";
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
