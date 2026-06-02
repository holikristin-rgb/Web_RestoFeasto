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
        Schema::create('menu', function (Blueprint $table) {
            $table->id('menu_id');
            $table->unsignedBigInteger('id_kategori');
            $table->string('nama_menu', 150);
            $table->decimal('harga', 10, 2);
            $table->integer('stok');
            $table->text('deskripsi');
            $table->string('gambar', 255)->nullable();
            $table->timestamps();
            
            $table->foreign('id_kategori')
                  ->references('id_kategori')
                  ->on('kategori_menu')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu');
    }
};
