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
    Schema::create('order_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('order_id')->constrained()->onDelete('cascade');
        $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Relasi ke products
        
        // --- Data Produk saat checkout ---
        $table->string('custom_id')->nullable(); // ID Produk custom
        $table->string('name'); // Simpan nama barang (biar aman kalau barang dihapus nanti)
        $table->string('category')->nullable(); // Kategori
        $table->string('length_diameter')->nullable(); // Panjang / Diameter
        $table->string('width')->nullable(); // Lebar
        
        // --- Harga Pilihan ---
        $table->integer('price_normal')->default(0); // Harga Normal
        $table->integer('price_medium')->default(0); // Harga Sedang
        $table->integer('price_high')->default(0); // Harga Tinggi
        
        // --- Informasi Order Item ---
        $table->integer('qty');
        $table->string('price_type')->default('normal'); // normal/medium/high
        $table->integer('price'); // Harga yang dipilih saat checkout
        $table->integer('subtotal');
        // --------------------------------
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
