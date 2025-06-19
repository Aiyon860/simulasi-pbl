<?php

namespace App\Observers;

use Illuminate\Http\Request;
use App\Helpers\TrackLogHelpers;
use App\Models\PenerimaanDiCabang;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class PenerimaanDiCabangObserver implements ShouldHandleEventsAfterCommit
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    
    /**
     * Handle the PenerimaanDiCabang "created" event.
     */
    public function created(PenerimaanDiCabang $penerimaanDiCabang): void
    {
        $logPesan = "Melakukan penerimaan di cabang dengan barang {$penerimaanDiCabang->barang->nama_barang} sejumlah {$penerimaanDiCabang->jumlah_barang} dengan jenis penerimaan berupa {$penerimaanDiCabang->jenisPenerimaan->nama_jenis_penerimaan} dari {$penerimaanDiCabang->asalBarang->nama_gudang_toko}";

        TrackLogHelpers::createLog(
            auth()->user()->id, 
            $this->request->ip(),
            $logPesan
        );
    }

    /**
     * Handle the PenerimaanDiCabang "updated" event.
     */
    public function updated(PenerimaanDiCabang $penerimaanDiCabang): void
    {
        $logPesan = '';
        
        if ($penerimaanDiCabang->wasChanged('id_verifikasi')) {
            $jenis_verifikasi = $penerimaanDiCabang->verifikasi->jenis_verifikasi;

            $logPesan .= match ($jenis_verifikasi) {
                'Terverifikasi' => "Memverifikasi salah satu laporan penerimaan barang di {$penerimaanDiCabang->cabang->nama_gudang_toko} dari {$penerimaanDiCabang->asalBarang->nama_gudang_toko}",
                'Tidak Diverifikasi' => "Tidak memverifikasi salah satu laporan penerimaan barang di {$penerimaanDiCabang->cabang->nama_gudang_toko} dari {$penerimaanDiCabang->asalBarang->nama_gudang_toko}",
            };
        } else if ($penerimaanDiCabang->wasChanged('flag')) {
            $logPesan .= "Menghapus salah satu laporan penerimaan barang di {$penerimaanDiCabang->cabang->nama_gudang_toko} dari {$penerimaanDiCabang->asalBarang->nama_gudang_toko}";
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
