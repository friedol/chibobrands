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
        Schema::table('enhanced_products', function (Blueprint $table) {
            // Add new base price fields (weight already exists)
            $table->decimal('retail_base_price', 12, 2)->nullable()->after('buying_price');
            $table->decimal('b2b_base_price', 12, 2)->nullable()->after('retail_base_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enhanced_products', function (Blueprint $table) {
            $table->dropColumn(['retail_base_price', 'b2b_base_price']);
        });
    }
};
