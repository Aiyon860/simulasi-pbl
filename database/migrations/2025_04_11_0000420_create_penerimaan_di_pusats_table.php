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
        Schema::create('penerimaan_di_pusats', function (Blueprint $table) {
            $table->id();
            $table->string('kode');
            
            $table->unsignedBigInteger('id_jenis_penerimaan');
            $table->foreign('id_jenis_penerimaan')
                ->references('id')
                ->on('jenis_penerimaans')
                ->cascadeOnUpdate();

            $table->unsignedBigInteger('id_asal_barang');
            $table->foreign('id_asal_barang')
                ->references('id')
                ->on('gudang_dan_tokos')
                ->cascadeOnUpdate();

            $table->unsignedBigInteger('id_barang');
            $table->foreign('id_barang')
                ->references('id')
                ->on('barangs')
                ->cascadeOnUpdate();

            $table->unsignedBigInteger('id_satuan_berat');
            $table->foreign('id_satuan_berat')
                ->references('id')
                ->on('satuan_berats')
                ->cascadeOnUpdate();

            $table->unsignedBigInteger('id_verifikasi')->default(1);
            $table->foreign('id_verifikasi')
                ->references('id')
                ->on('verifikasi')
                ->cascadeOnUpdate();

            $table->unsignedBigInteger('id_laporan_pengiriman')->nullable();
            $table->foreign('id_laporan_pengiriman')
                ->references('id')
                ->on('supplier_ke_pusats')
                ->cascadeOnUpdate();

            $table->unsignedBigInteger('id_laporan_retur')->nullable();
            $table->foreign('id_laporan_retur')
                ->references('id')
                ->on('cabang_ke_pusats')
                ->cascadeOnUpdate();

            $table->Integer('berat_satuan_barang');
            $table->Integer('jumlah_barang');
            $table->integer('diterima')->default(0); // 0 atau 1            
            $table->dateTime('tanggal');
            $table->integer('flag')->default(1);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penerimaan_di_pusats');
    }
};
