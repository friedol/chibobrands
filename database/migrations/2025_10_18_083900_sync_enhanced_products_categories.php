<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // This migration helps sync category names in enhanced_products
        // with the actual categories in the categories table
        
        // Map old category names to new ones based on your categories table
        $categoryMappings = [
            'Backdrops' => 'Large Format',
            'Canopies' => 'Large Format',
            'Billboards' => 'Large Format',
            'Promo Tables' => 'Advertising Stands',
            'Brochures' => 'Flyers',
            'Roll-up Banners' => 'Advertising Stands',
            // Keep these as they already exist
            'Stickers' => 'Stickers',
            'Flyers' => 'Flyers',
            'Exhibition Tents' => 'Exhibition Tents',
        ];

        foreach ($categoryMappings as $oldCategory => $newCategory) {
            DB::table('enhanced_products')
                ->where('category', $oldCategory)
                ->update(['category' => $newCategory]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally revert the changes
        $reverseMappings = [
            'Large Format' => 'Backdrops',
            'Advertising Stands' => 'Promo Tables',
        ];

        foreach ($reverseMappings as $newCategory => $oldCategory) {
            DB::table('enhanced_products')
                ->where('category', $newCategory)
                ->limit(1)
                ->update(['category' => $oldCategory]);
        }
    }
};
