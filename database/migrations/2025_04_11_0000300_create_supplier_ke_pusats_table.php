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
        Schema::create('supplier_ke_pusats', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string(column: 'kode');
            $table->unsignedBigInteger('id_supplier');
            $table->foreign('id_supplier')
                ->references('id')
                ->on('gudang_dan_tokos')
                ->cascadeOnUpdate();
            
            $table->unsignedBigInteger('id_pusat');
            $table->foreign('id_pusat')
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

            $table->unsignedBigInteger('id_kurir'); // Foreign Key
            $table->foreign('id_kurir')->references('id')->on('kurirs')->cascadeOnUpdate();

            $table->unsignedBigInteger('id_status'); // Foreign Key
            $table->foreign('id_status')->references('id')->on('statuses')->cascadeOnUpdate();

            $table->unsignedBigInteger('id_verifikasi')->default(1);
            $table->foreign('id_verifikasi')
                ->references('id')
                ->on('verifikasi')
                ->cascadeOnUpdate();
            
            $table->dateTime('tanggal');
            $table-> integer(column: 'flag')->default(value: 1);
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_ke_pusats');
    }
};
