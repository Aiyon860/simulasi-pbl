<?php

namespace App\Helpers;

use Carbon\Carbon;

class ShippingAndReturnCodeHelpers
{
    /**
     * Generate kode untuk Toko ke Cabang (TKC)
     */
    public static function generateTokoKeCabangCode($datetime): string
    {
        return 'TKC-' . self::getFormattedDateTime($datetime);
    }

    /**
     * Generate kode untuk Cabang ke Pusat (CKP)
     */
    public static function generateCabangKePusatCode($datetime): string
    {
        return 'CKP-' . self::getFormattedDateTime($datetime);
    }

    /**
     * Generate kode untuk Cabang ke Toko (CKT)
     */
    public static function generateCabangKeTokoCode($datetime): string
    {
        return 'CKT-' . self::getFormattedDateTime($datetime);
    }

    /**
     * Generate kode untuk Pusat ke Cabang (PKC)
     */
    public static function generatePusatKeCabangCode($datetime): string
    {
        return 'PKC-' . self::getFormattedDateTime($datetime);
    }

    /**
     * Generate kode untuk Pusat ke Supplier (PKS)
     */
    public static function generatePusatKeSupplierCode($datetime): string
    {
        return 'PKS-' . self::getFormattedDateTime($datetime);
    }

    /**
     * Generate kode untuk Supplier ke Pusat (SKP)
     */
    public static function generateSupplierKePusatCode($datetime): string
    {
        return 'SKP-' . self::getFormattedDateTime($datetime);
    }

    /**
     * Helper function untuk mendapatkan format datetime yang sesuai
     */
    private static function getFormattedDateTime(Carbon $datetime): string
    {
        return $datetime->format('dmYHis');
    }
}