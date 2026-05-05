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
        // First, migrate existing base_price data to both retail_base_price and b2b_base_price
        \DB::statement("
            UPDATE enhanced_products 
            SET 
                retail_base_price = CASE 
                    WHEN retail_base_price IS NULL OR retail_base_price = 0 
                    THEN base_price 
                    ELSE retail_base_price 
                END,
                b2b_base_price = CASE 
                    WHEN b2b_base_price IS NULL OR b2b_base_price = 0 
                    THEN base_price 
                    ELSE b2b_base_price 
                END
            WHERE base_price IS NOT NULL AND base_price > 0
        ");

        // Now drop the legacy base_price column
        Schema::table('enhanced_products', function (Blueprint $table) {
            $table->dropColumn('base_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add the base_price column
        Schema::table('enhanced_products', function (Blueprint $table) {
            $table->decimal('base_price', 12, 2)->nullable()->after('buying_price');
        });

        // Restore base_price from retail_base_price (as a fallback)
        \DB::statement("
            UPDATE enhanced_products 
            SET base_price = retail_base_price 
            WHERE base_price IS NULL AND retail_base_price IS NOT NULL
        ");
    }
};
