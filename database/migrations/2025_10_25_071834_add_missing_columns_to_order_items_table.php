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
        Schema::table('order_items', function (Blueprint $table) {
            $table->json('product_variations')->nullable()->after('variants');
            $table->json('product_addons')->nullable()->after('product_variations');
            $table->json('custom_inputs')->nullable()->after('product_addons');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['product_variations', 'product_addons', 'custom_inputs']);
        });
    }
};
