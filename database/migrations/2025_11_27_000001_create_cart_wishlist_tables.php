<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keranjang', function (Blueprint $table) {
            $table->id('keranjang_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('produk_id');
            $table->string('size')->default('M');
            $table->integer('jumlah')->default(1);
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('produk_id')->references('produk_id')->on('produk')->onDelete('cascade');
        });
        
        Schema::create('wishlist', function (Blueprint $table) {
            $table->id('wishlist_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('produk_id');
            $table->date('tanggal_ditambahkan')->default(now());
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('produk_id')->references('produk_id')->on('produk')->onDelete('cascade');
        });
        
        Schema::create('ulasan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('produk_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('rating')->between(1, 5);
            $table->text('komentar');
            $table->dateTime('tanggal_ulasan')->default(now());
            $table->timestamps();
            
            $table->foreign('produk_id')->references('produk_id')->on('produk')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ulasan');
        Schema::dropIfExists('wishlist');
        Schema::dropIfExists('keranjang');
    }
};
