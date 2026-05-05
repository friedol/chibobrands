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
            $table->json('variants')->nullable()->after('subtotal');
            $table->string('product_barcode')->nullable()->after('variants');
            $table->string('product_name')->nullable()->after('product_barcode');
            $table->string('channel')->nullable()->after('product_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['variants', 'product_barcode', 'product_name', 'channel']);
        });
    }
};