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
        Schema::create('gudang_dan_tokos', function (Blueprint $table) {
            $table->id();
            $table->string('nama_gudang_toko');
            $table->integer('kategori_bangunan');
            $table->string('alamat')->nullable();
            $table->string('no_telepon')->nullable();
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
        Schema::dropIfExists('gudangs');
    }
};
