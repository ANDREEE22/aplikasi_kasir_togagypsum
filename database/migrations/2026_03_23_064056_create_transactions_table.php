<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ===== TABLE TRANSACTIONS =====
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code')->unique();
            $table->decimal('total_amount', 12, 2);
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('dp_nominal', 12, 2)->default(0);
            $table->string('payment_method'); // cash, transfer, dp, bayarTempat
            $table->string('shipping_method'); // ambil, kirim
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index('transaction_code');
            $table->index('payment_method');
            $table->index('status');
        });

        // ===== TABLE TRANSACTION ITEMS =====
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->string('product_name');
            $table->integer('quantity');
            $table->decimal('price', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
            
            $table->foreign('transaction_id')
                ->references('id')
                ->on('transactions')
                ->onDelete('cascade');
        });

        // ===== TABLE PAYMENT PROOFS =====
        Schema::create('payment_proofs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->string('payment_method'); // transfer, dp
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_url');
            $table->integer('file_size');
            $table->string('mime_type')->default('image/jpeg');
            $table->text('description')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
            
            $table->foreign('transaction_id')
                ->references('id')
                ->on('transactions')
                ->onDelete('cascade');
            
            $table->index('payment_method');
            $table->index('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_proofs');
        Schema::dropIfExists('transaction_items');
        Schema::dropIfExists('transactions');
    }
};