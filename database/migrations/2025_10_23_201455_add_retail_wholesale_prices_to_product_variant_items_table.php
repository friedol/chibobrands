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
        Schema::table('product_variant_items', function (Blueprint $table) {
            $table->decimal('retail_price', 10, 2)->default(0)->after('price');
            $table->decimal('wholesale_price', 10, 2)->default(0)->after('retail_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variant_items', function (Blueprint $table) {
            $table->dropColumn(['retail_price', 'wholesale_price']);
        });
    }
};
