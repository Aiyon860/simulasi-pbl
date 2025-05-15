<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // pengiriman
        DB::unprepared("
            CREATE TRIGGER after_pusat_ke_cabang_insert
            AFTER INSERT ON pusat_ke_cabangs
            FOR EACH ROW
            BEGIN
                UPDATE detail_gudangs
                SET jumlah_stok = jumlah_stok - NEW.jumlah_barang
                WHERE 
                    id_gudang = NEW.id_pusat
                    AND
                    id_barang = NEW.id_barang
                LIMIT 1;
            END;
        ");

        DB::unprepared("
            CREATE TRIGGER after_cabang_ke_toko_insert
            AFTER INSERT ON cabang_ke_tokos
            FOR EACH ROW
            BEGIN
                UPDATE detail_gudangs
                SET jumlah_stok = jumlah_stok - NEW.jumlah_barang
                WHERE 
                    id_gudang = NEW.id_cabang
                    AND
                    id_barang = NEW.id_barang
                LIMIT 1;
            END;
        ");

        // penerimaan
        DB::unprepared("
            CREATE TRIGGER after_penerimaan_di_pusat_insert
            AFTER INSERT ON penerimaan_di_pusats
            FOR EACH ROW
            BEGIN
                UPDATE detail_gudangs
                SET jumlah_stok = jumlah_stok + NEW.jumlah_barang
                WHERE 
                    id_gudang = 1
                    AND
                    id_barang = NEW.id_barang
                LIMIT 1;
            END;
        ");

        DB::unprepared("
            CREATE TRIGGER after_penerimaan_di_cabang_insert
            AFTER INSERT ON penerimaan_di_cabangs
            FOR EACH ROW
            BEGIN
                UPDATE detail_gudangs
                SET jumlah_stok = jumlah_stok + NEW.jumlah_barang
                WHERE 
                    id_gudang = NEW.id_cabang
                    AND
                    id_barang = NEW.id_barang
                LIMIT 1;
            END;
        ");

        // retur
        DB::unprepared("
            CREATE TRIGGER after_cabang_ke_pusat_insert
            AFTER INSERT ON cabang_ke_pusats
            FOR EACH ROW
            BEGIN
                UPDATE detail_gudangs
                SET jumlah_stok = jumlah_stok - NEW.jumlah_barang
                WHERE 
                    id_gudang = NEW.id_cabang
                    AND
                    id_barang = NEW.id_barang
                LIMIT 1;
            END;
        ");

        DB::unprepared("
            CREATE TRIGGER after_pusat_ke_supplier_insert
            AFTER INSERT ON pusat_ke_suppliers
            FOR EACH ROW
            BEGIN
                UPDATE detail_gudangs
                SET jumlah_stok = jumlah_stok - NEW.jumlah_barang
                WHERE 
                    id_gudang = NEW.id_pusat
                    AND
                    id_barang = NEW.id_barang
                LIMIT 1;
            END;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS after_pusat_ke_cabang_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS after_cabang_ke_toko_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS after_penerimaan_di_pusat_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS after_penerimaan_di_cabang_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS after_cabang_ke_pusat_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS after_pusat_ke_supplier_insert');
    }
};
