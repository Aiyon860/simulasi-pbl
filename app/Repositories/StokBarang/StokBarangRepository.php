<?php

namespace App\Repositories\StokBarang;

use App\Models\DetailGudang;

class StokBarangRepository
{
    public function getTopTenLowestStock(bool $isPusat = true, int $idCabang = null)
    {
        if ($isPusat) { // Superadmin / Supervisor
            return DetailGudang::with(['barang:id,nama_barang', 'gudang:id,nama_gudang_toko'])
                ->where('jumlah_stok', '<=', 5)
                ->orderBy('jumlah_stok', 'asc')
                ->take(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'nama_barang' => $item->barang->nama_barang,
                        'nama_gudang' => $item->gudang->nama_gudang_toko,
                        'jumlah_stok' => $item->jumlah_stok,
                    ];
                });
        } else { // Admin Cabang
            return DetailGudang::with(['barang:id,nama_barang'])
                ->where('jumlah_stok', '<=', 5)
                ->where('id_gudang', $idCabang)
                ->orderBy('jumlah_stok', 'asc')
                ->take(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'nama_barang' => $item->barang->nama_barang,
                        'jumlah_stok' => $item->jumlah_stok,
                    ];
                });
        }
    }
}