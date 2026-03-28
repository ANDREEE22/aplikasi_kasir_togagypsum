<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Tambahkan kolom dp_nominal setelah shipping_cost
            if (!Schema::hasColumn('orders', 'dp_nominal')) {
                $table->decimal('dp_nominal', 15, 2)->default(0)->after('shipping_cost');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'dp_nominal')) {
                $table->dropColumn('dp_nominal');
            }
        });
    }
};