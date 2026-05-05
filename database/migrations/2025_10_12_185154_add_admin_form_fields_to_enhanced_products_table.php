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
            // Basic product fields
            $table->decimal('base_price', 10, 2)->nullable()->after('buying_price');
            $table->decimal('weight', 8, 2)->nullable()->after('base_price');
            $table->string('dimensions')->nullable()->after('weight');
            $table->enum('availability', ['in_stock', 'out_of_stock', 'custom'])->default('in_stock')->after('dimensions');
            $table->text('features')->nullable()->after('availability');
            
            // Product settings
            $table->boolean('is_active')->default(true)->after('features');
            $table->boolean('is_wholesale')->default(false)->after('is_active');
            $table->boolean('customization_allowed')->default(false)->after('is_wholesale');
            $table->boolean('track_stock')->default(true)->after('customization_allowed');
            
            // Stock management
            $table->integer('stock_quantity')->default(0)->after('track_stock');
            $table->integer('low_stock_threshold')->default(10)->after('stock_quantity');
            $table->integer('min_quantity')->default(1)->after('low_stock_threshold');
            $table->integer('max_quantity')->default(100)->after('min_quantity');
            
            // SEO and ordering
            $table->integer('sort_order')->default(0)->after('max_quantity');
            $table->string('meta_title')->nullable()->after('sort_order');
            $table->text('meta_description')->nullable()->after('meta_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enhanced_products', function (Blueprint $table) {
            $table->dropColumn([
                'base_price', 'weight', 'dimensions', 'availability', 'features',
                'is_active', 'is_wholesale', 'customization_allowed', 'track_stock',
                'stock_quantity', 'low_stock_threshold', 'min_quantity', 'max_quantity',
                'sort_order', 'meta_title', 'meta_description'
            ]);
        });
    }
};