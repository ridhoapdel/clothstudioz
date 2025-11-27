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
        Schema::table('produk', function (Blueprint $table) {
            $table->integer('stok_s')->default(0)->after('stok');
            $table->integer('stok_m')->default(0)->after('stok_s');
            $table->integer('stok_l')->default(0)->after('stok_m');
            $table->integer('stok_xl')->default(0)->after('stok_l');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->dropColumn(['stok_s', 'stok_m', 'stok_l', 'stok_xl']);
        });
    }
};
