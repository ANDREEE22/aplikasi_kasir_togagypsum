<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop tabel lama
        Schema::dropIfExists('projects');
        Schema::dropIfExists('project_items');

        // Tabel projects (baru)
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Kolom 1: Nama
            $table->string('customer_name');
            
            // Kolom 2: Alamat
            $table->text('address');
            
            // Kolom 3: No HP (BARU)
            $table->string('phone_number')->nullable();
            
            // Kolom 4: Tgl Masuk (auto dari created_at)
            // created_at bawaan Laravel
            
            // Kolom 5: Status (4 pilihan)
            $table->enum('status', ['proses', 'belum_bayar', 'dp', 'selesai'])->default('proses');
            
            // Kolom 6: Tgl Pemasangan
            $table->date('installation_date')->nullable();
            
            // Kolom 7: Lis dan Harga (handled via project_items table)
            // Relasi one-to-many ke project_items
            
            // Kolom 8: Catatan
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });

        // Tabel project_items (untuk menyimpan barang/lis dan harga)
        Schema::create('project_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            
            // Nama barang (misal: Lis plafon atas)
            $table->string('item_name');
            
            // Harga barang
            $table->integer('item_price');
            
            // Jumlah/qty
            $table->integer('quantity')->default(1);
            
            // Total untuk barang ini (item_price * quantity)
            $table->integer('subtotal');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_items');
        Schema::dropIfExists('projects');
    }
};