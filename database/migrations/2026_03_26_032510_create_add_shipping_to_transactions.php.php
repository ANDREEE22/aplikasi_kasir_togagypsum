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
        // ✅ ADD SHIPPING FIELDS KE TRANSACTIONS TABLE
        Schema::table('transactions', function (Blueprint $table) {
            // Check dan add shipping_cost jika belum ada
            if (!Schema::hasColumn('transactions', 'shipping_cost')) {
                $table->decimal('shipping_cost', 12, 2)->default(0)->after('total_amount');
            }

            // Check dan add shipping_method jika belum ada
            if (!Schema::hasColumn('transactions', 'shipping_method')) {
                $table->string('shipping_method')->default('ambil')->after('shipping_cost');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Hapus columns saat rollback
            if (Schema::hasColumn('transactions', 'shipping_cost')) {
                $table->dropColumn('shipping_cost');
            }
            if (Schema::hasColumn('transactions', 'shipping_method')) {
                $table->dropColumn('shipping_method');
            }
        });
    }
};