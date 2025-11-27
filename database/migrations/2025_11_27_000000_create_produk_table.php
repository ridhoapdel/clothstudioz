<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produk', function (Blueprint $table) {
            $table->id('produk_id');
            $table->string('nama_produk');
            $table->text('deskripsi');
            $table->decimal('harga', 10, 2);
            $table->integer('stok');
            $table->string('gambar_produk');
            $table->timestamps();
        });
        
        Schema::create('barang_diskon', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('produk_id');
            $table->integer('diskon_persen');
            $table->decimal('harga_diskon', 10, 2);
            $table->date('mulai_diskon');
            $table->date('selesai_diskon');
            $table->foreign('produk_id')->references('produk_id')->on('produk')->onDelete('cascade');
        });
        
        Schema::create('user', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('email')->nullable();
            $table->string('role')->default('user');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_diskon');
        Schema::dropIfExists('produk');
        Schema::dropIfExists('user');
    }
};
