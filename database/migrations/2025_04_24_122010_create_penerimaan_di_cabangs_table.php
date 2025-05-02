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
        Schema::create('penerimaan_di_cabangs', function (Blueprint $table) {
            $table->id();
            
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

            $table->integer('jumlah');
            $table->dateTime('tanggal');
            $table->integer('flag')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penerimaan_di_cabangs');
    }
};
