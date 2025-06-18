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
        Schema::create('cabang_ke_tokos', function (Blueprint $table) {
            $table->id();
            $table->string('kode');

            $table->unsignedBigInteger('id_kurir'); // Foreign Key
            $table->foreign('id_kurir')->references('id')->on('kurirs')->cascadeOnUpdate();

            $table->unsignedBigInteger('id_status'); // Foreign Key
            $table->foreign('id_status')->references('id')->on('statuses')->cascadeOnUpdate();
            $table->unsignedBigInteger('id_cabang');
            $table->foreign('id_cabang')
                ->references('id')
                ->on('gudang_dan_tokos')
                ->cascadeOnUpdate();
            $table->unsignedBigInteger('id_toko');
            $table->foreign('id_toko')
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
            $table->Integer('berat_satuan_barang');
            $table->Integer('jumlah_barang');
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
        Schema::dropIfExists('cabang_ke_tokos');
    }
};
