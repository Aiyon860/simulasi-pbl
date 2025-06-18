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
        Schema::create('pusat_ke_suppliers', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('kode'); // Kolom wajib
            $table->unsignedBigInteger('id_supplier'); // Foreign Key
            $table->unsignedBigInteger('id_pusat'); // Foreign Key
            $table->unsignedBigInteger('id_barang');
            $table->unsignedBigInteger('id_satuan_berat'); // Foreign Key
            $table->unsignedBigInteger('id_kurir'); // Foreign Key
            $table->unsignedBigInteger('id_status'); // Foreign Key
            $table->integer('berat_satuan_barang'); // Foreign Key
            $table->bigInteger('jumlah_barang'); // Kolom wajib
            $table->datetime('tanggal'); // Kolom wajib
            $table->timestamps();
            $table->softDeletes();
            $table->integer('flag')->default(1); 
            // Foreign Key Constraints
            $table->foreign('id_supplier')->references('id')->on('gudang_dan_tokos')->cascadeOnUpdate();
            $table->foreign('id_pusat')->references('id')->on('gudang_dan_tokos')->cascadeOnUpdate();
            $table->foreign('id_barang')->references('id')->on('barangs')->cascadeOnUpdate();
            $table->foreign('id_satuan_berat')->references('id')->on('satuan_berats')->cascadeOnUpdate();
            $table->foreign('id_kurir')->references('id')->on('kurirs')->cascadeOnUpdate();
            $table->foreign('id_status')->references('id')->on('statuses')->cascadeOnUpdate();

            $table->unsignedBigInteger('id_verifikasi')->default(1);
            $table->foreign('id_verifikasi')
                ->references('id')
                ->on('verifikasi')
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pusat_ke_suppliers');
    }
};
