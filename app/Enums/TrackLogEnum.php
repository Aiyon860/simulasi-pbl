<?php

namespace App\Enums;

// Ini disebut "Backed Enum", dimana setiap case memiliki nilai string yang sesuai.
enum TrackLogEnum: string
{
    case PengirimanPusatKeCabang = 'Pengiriman - Pusat Ke Cabang';
    case PengirimanCabangKeToko = 'Pengiriman - Cabang Ke Toko';
    case PenerimaanPusatDariSupplier = 'Penerimaan Di Pusat - Dari Supplier';
    case PenerimaanPusatDariCabang = 'Penerimaan Di Pusat - Dari Cabang';
    case PenerimaanCabangDariPusat = 'Penerimaan Di Cabang - Dari Pusat';
    case PenerimaanCabangDariToko = 'Penerimaan Di Cabang - Dari Toko';
    case ReturCabangKePusat = 'Retur - Cabang Ke Pusat';
    case ReturPusatKeSupplier = 'Retur - Pusat Ke Supplier';
    case PerubahanStatusOpname = 'Perubahan Status Opname';
    
    // Helper function untuk mendapatkan semua nilai string (opsional tapi sangat berguna)
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}