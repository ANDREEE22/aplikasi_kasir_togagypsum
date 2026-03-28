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
        Schema::create('payment_proofs', function (Blueprint $table) {
            $table->id();
            
            // ===== RELATIONS =====
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            
            // ===== PAYMENT INFO =====
            $table->string('order_code')->unique(); // ORD_1711270800000
            $table->string('payment_method'); // cash, transfer, dp, bayarTempat
            $table->string('transaction_id')->nullable(); // TRX_1711270800000
            
            // ===== FILE INFO =====
            $table->string('file_name'); // payment_proof_transfer_1711270800000.jpg
            $table->string('file_path'); // payment_proofs/ORD_123/payment_proof_transfer_...
            $table->string('file_url'); // https://domain.com/storage/payment_proofs/...
            $table->integer('file_size'); // bytes
            $table->string('mime_type')->default('image/jpeg');
            $table->string('original_name')->nullable();
            
            // ===== VERIFICATION =====
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            
            // ===== METADATA =====
            $table->decimal('amount', 12, 2)->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->string('uploader_ip')->nullable();
            $table->string('uploader_user_agent')->nullable();
            
            // ===== TIMESTAMPS =====
            $table->timestamps();
            
            // ===== INDEXES =====
            $table->index('order_code');
            $table->index('payment_method');
            $table->index('status');
            $table->index('created_at');
            
            // ===== FOREIGN KEYS =====
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_proofs');
    }
};