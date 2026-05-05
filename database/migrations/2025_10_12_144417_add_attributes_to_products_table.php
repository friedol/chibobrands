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
        Schema::table('products', function (Blueprint $table) {
            // Only add columns that don't already exist
            if (!Schema::hasColumn('products', 'brand')) {
                $table->string('brand')->nullable()->after('name');
            }
            if (!Schema::hasColumn('products', 'color_options')) {
                $table->json('color_options')->nullable()->after('material'); // store e.g. ["Red","Black"]
            }
            if (!Schema::hasColumn('products', 'size_options')) {
                $table->json('size_options')->nullable()->after('color_options'); // store e.g. ["S","M","L","Custom"]
            }
            if (!Schema::hasColumn('products', 'printing_type')) {
                $table->string('printing_type')->nullable()->after('size_options'); // e.g. "Digital", "Screen"
            }
            if (!Schema::hasColumn('products', 'specifications')) {
                $table->json('specifications')->nullable()->after('printing_type'); // key-value JSON
            }
            if (!Schema::hasColumn('products', 'price_range_display')) {
                $table->string('price_range_display')->nullable()->after('wholesale_price'); // e.g. "1,100 - 1,500"
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['brand','color_options','size_options','printing_type','specifications','price_range_display']);
        });
    }
};
