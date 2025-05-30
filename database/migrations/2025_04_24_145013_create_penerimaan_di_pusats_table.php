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

            $table->Integer('berat_satuan_barang');
            $table->Integer('jumlah_barang');
            $table->dateTime('tanggal');
            $table->integer('flag')->default(1);

            $table->unsignedBigInteger('id_kurir');
            $table->foreign('id_kurir')->references('id')->on('kurirs')->cascadeOnUpdate();

            $table->unsignedBigInteger('id_status');
            $table->foreign('id_status')->references('id')->on('statuses')->cascadeOnUpdate();

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
