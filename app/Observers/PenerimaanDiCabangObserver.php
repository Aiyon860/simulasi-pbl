<?php

namespace App\Observers;

use App\Models\TokoKeCabang;
use Illuminate\Http\Request;
use App\Models\PusatKeCabang;
use App\Helpers\TrackLogHelpers;
use App\Models\PenerimaanDiCabang;
use Illuminate\Support\Facades\DB;
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
        $logPesan = "Melakukan penerimaan di cabang dengan kode {$penerimaanDiCabang->kode} barang {$penerimaanDiCabang->barang->nama_barang} sejumlah {$penerimaanDiCabang->jumlah_barang} dengan jenis penerimaan berupa {$penerimaanDiCabang->jenisPenerimaan->nama_jenis_penerimaan} dari {$penerimaanDiCabang->asalBarang->nama_gudang_toko}";

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
        } else if ($penerimaanDiCabang->wasChanged('diterima')) {
            $logPesan .= "Melakukan penerimaan di cabang dengan kode {$penerimaanDiCabang->kode} barang {$penerimaanDiCabang->barang->nama_barang} sejumlah {$penerimaanDiCabang->jumlah_barang} dengan jenis penerimaan berupa {$penerimaanDiCabang->jenisPenerimaan->nama_jenis_penerimaan} dari {$penerimaanDiCabang->asalBarang->nama_gudang_toko}";

            $id_laporan_pengiriman = $penerimaanDiCabang->id_laporan_pengiriman ?? null;

            if ($id_laporan_pengiriman != null) {
                $pusatKeCabang = PusatKeCabang::findOrFail($id_laporan_pengiriman);

                DB::transaction(function () use ($pusatKeCabang) {
                    $pusatKeCabang->update(['id_status' => 4]);
                }, 3);
            } 
            
            $id_laporan_retur = $penerimaanDiCabang->id_laporan_retur ?? null;
            
            if ($id_laporan_retur != null) {
                $tokoKeCabang = TokoKeCabang::findOrFail($id_laporan_pengiriman);

                DB::transaction(function () use ($tokoKeCabang) {
                    $tokoKeCabang->update(['id_status' => 4]);
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
