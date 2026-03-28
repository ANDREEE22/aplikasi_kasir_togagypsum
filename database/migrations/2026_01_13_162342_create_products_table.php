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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // --- Tambahan Kolom Baru ---
            $table->string('custom_id')->unique()->nullable(); // ID Produk isi sendiri
            $table->string('name');
            $table->string('category')->nullable();           // Kategori
            $table->string('length_diameter')->nullable();    // Panjang / Diameter
            $table->string('width')->nullable();              // Lebar
            
            // 3 Box Harga
            $table->integer('price_normal')->default(0);      // Harga Normal
            $table->integer('price_medium')->default(0);      // Harga Sedang
            $table->integer('price_high')->default(0);        // Harga Tinggi
            // ---------------------------
            
            $table->integer('stock');
            $table->string('image')->nullable(); 
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};