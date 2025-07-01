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
        Schema::create('detail_gudangs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_gudang');
            $table->foreign('id_gudang')
                ->references('id')
                ->on('gudang_dan_tokos')
                ->cascadeOnUpdate();

            $table->unsignedBigInteger('id_barang');
            $table->foreign('id_barang')
                ->references('id')
                ->on('barangs')
                ->cascadeOnUpdate();

            $table->bigInteger('jumlah_stok')->default(0);
            $table->integer('stok_opname')->default(0);
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
        Schema::dropIfExists('detail_gudangs');
    }
};
