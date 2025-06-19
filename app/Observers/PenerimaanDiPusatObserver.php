<?php

namespace App\Observers;

use App\Models\CabangKePusat;
use Illuminate\Http\Request;
use App\Models\SupplierKePusat;
use App\Helpers\TrackLogHelpers;
use App\Models\PenerimaanDiPusat;
use Illuminate\Support\Facades\DB;
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
        $logPesan = "Melakukan penerimaan di pusat dengan kode {$penerimaanDiPusat->kode} berupa barang {$penerimaanDiPusat->barang->nama_barang} sejumlah {$penerimaanDiPusat->jumlah_barang} dengan jenis penerimaan berupa {$penerimaanDiPusat->jenisPenerimaan->nama_jenis_penerimaan} dari {$penerimaanDiPusat->asalBarang->nama_gudang_toko}";

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
        } else if ($penerimaanDiPusat->wasChanged('diterima')) {
            $logPesan .= "Melakukan penerimaan di pusat dengan kode {$penerimaanDiPusat->kode} berupa barang {$penerimaanDiPusat->barang->nama_barang} sejumlah {$penerimaanDiPusat->jumlah_barang} dengan jenis penerimaan berupa {$penerimaanDiPusat->jenisPenerimaan->nama_jenis_penerimaan} dari {$penerimaanDiPusat->asalBarang->nama_gudang_toko}";

            $id_laporan_pengiriman = $penerimaanDiPusat->id_laporan_pengiriman ?? null;

            if ($id_laporan_pengiriman != null) {
                $supplierKePusat = SupplierKePusat::findOrFail($id_laporan_pengiriman);

                DB::transaction(function () use ($supplierKePusat) {
                    $supplierKePusat->update(['id_status' => 4]);
                }, 3);
            } 
            
            $id_laporan_retur = $penerimaanDiPusat->id_laporan_retur ?? null;
            
            if ($id_laporan_retur != null) {
                $cabangKePusat = CabangKePusat::findOrFail($id_laporan_retur);

                DB::transaction(function () use ($cabangKePusat) {
                    $cabangKePusat->update(['id_status' => 4]);
                }, 3);
            } 
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
