<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'shipping_cost')) {
                $table->integer('shipping_cost')->default(0)->after('total');
            }
            if (!Schema::hasColumn('orders', 'shipping_method')) {
                $table->string('shipping_method')->default('ambil')->after('shipping_cost');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'shipping_cost')) {
                $table->dropColumn('shipping_cost');
            }
            if (Schema::hasColumn('orders', 'shipping_method')) {
                $table->dropColumn('shipping_method');
            }
        });
    }
};