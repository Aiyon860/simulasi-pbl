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
        Schema::create('track_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_user');
            $table->foreign('id_user')
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate();

            $table->string('ip_address', 45); // untuk IPv6 juga
            $table->enum('aktivitas', [
                'Pengiriman - Pusat Ke Cabang', 
                'Pengiriman - Cabang Ke Toko', 
                'Penerimaan Di Pusat - Dari Supplier', 
                'Penerimaan Di Pusat - Dari Cabang', 
                'Penerimaan Di Cabang - Dari Pusat', 
                'Penerimaan Di Cabang - Dari Toko', 
                'Retur - Cabang Ke Pusat', 
                'Retur - Pusat Ke Supplier', 
                'Perubahan Status Opname'
            ]);
            $table->datetime('tanggal_aktivitas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('track_logs');
    }
};
