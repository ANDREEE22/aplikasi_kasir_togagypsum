<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lonjaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Informasi Pelanggan
            $table->string('customer_name');
            $table->string('address');
            $table->string('phone_number');
            
            // Tanggal
            $table->date('entry_date'); // Tgl masuk
            $table->date('delivery_deadline'); // Deadline kirim
            
            // Pengiriman
            $table->enum('delivery_type', ['diambil_sendiri', 'diantar'])->default('diambil_sendiri');
            
            // Harga
            $table->decimal('total_price', 15, 2)->default(0);
            
            // Pembayaran
            $table->enum('payment_status', ['lunas', 'dp', 'belum_bayar'])->default('belum_bayar');
            
            // Status Proses
            $table->enum('order_status', [
                'sudah_bayar_belum_kirim',
                'sudah_kirim_belum_bayar',
                'proses',
                'transaksi_selesai'
            ])->default('proses');
            
            // Keterangan
            $table->longText('notes')->nullable();
            
            $table->timestamps();
        });

        Schema::create('lonjaran_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lonjaran_id')->constrained('lonjaran')->onDelete('cascade');
            $table->string('item_name');
            $table->decimal('item_price', 15, 2);
            $table->integer('quantity');
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lonjaran_items');
        Schema::dropIfExists('lonjaran');
    }
};