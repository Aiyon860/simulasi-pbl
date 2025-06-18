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
            $table->id(); // ID track log (primary key)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('ip_address', 45); // untuk IPv6 juga
            $table->enum('aktivitas', ['pengiriman', 'penerimaan', 'retur', 'perubahan status opname']);
            $table->timestamp('tanggal_aksi');
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
