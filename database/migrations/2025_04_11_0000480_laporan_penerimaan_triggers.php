<?php

use App\Helpers\CodeHelpers;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Dari Pusat Ke Cabang
        DB::unprepared("
            CREATE TRIGGER after_terkirim_pusat_ke_cabang
            AFTER UPDATE ON pusat_ke_cabangs
            FOR EACH ROW
            BEGIN
                -- Cek jika status yang baru adalah 3 (terkirim)
                IF NEW.id_status = 3 THEN
                    INSERT INTO penerimaan_di_cabangs (
                        kode,
                        id_jenis_penerimaan,
                        id_asal_barang,
                        id_cabang,
                        id_barang,
                        id_satuan_berat,
                        id_laporan_pengiriman,
                        berat_satuan_barang,
                        jumlah_barang,
                        tanggal,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        CONCAT('PDC-', DATE_FORMAT(NOW(), '%d%m%Y%H%i%s')), -- Kode
                        1, -- JENIS PENERIMAAN: Pengiriman
                        NEW.id_pusat,
                        NEW.id_cabang,
                        NEW.id_barang,
                        NEW.id_satuan_berat,
                        NEW.id,
                        NEW.berat_satuan_barang,
                        NEW.jumlah_barang,
                        NOW(),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ");

        // Dari Cabang Ke Pusat
        DB::unprepared("
            CREATE TRIGGER after_terkirim_cabang_ke_pusat
            AFTER UPDATE ON cabang_ke_pusats
            FOR EACH ROW
            BEGIN
                -- Cek jika status yang baru adalah 3 (terkirim)
                IF NEW.id_status = 3 THEN
                    INSERT INTO penerimaan_di_pusats (
                        kode,
                        id_jenis_penerimaan,
                        id_asal_barang,
                        id_barang,
                        id_satuan_berat,
                        id_laporan_retur,
                        berat_satuan_barang,
                        jumlah_barang,
                        tanggal,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        CONCAT('PDP-', DATE_FORMAT(NOW(), '%d%m%Y%H%i%s')), -- Kode
                        2, -- JENIS PENERIMAAN: Retur
                        NEW.id_cabang,
                        NEW.id_barang,
                        NEW.id_satuan_berat,
                        NEW.id,
                        NEW.berat_satuan_barang,
                        NEW.jumlah_barang,
                        NOW(),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS after_terkirim_pusat_ke_cabang');
        DB::unprepared('DROP TRIGGER IF EXISTS after_terkirim_cabang_ke_pusat');
    }
};
