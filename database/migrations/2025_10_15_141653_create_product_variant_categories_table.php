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
        Schema::create('product_variant_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('enhanced_products')->onDelete('cascade');
            $table->string('category'); // e.g., Color, Size, Material, Custom
            $table->decimal('price_adjustment', 12, 2)->default(0); // optional % or absolute later
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        // Now that product_variant_categories exists, add the foreign key for items
        Schema::table('product_variant_items', function (Blueprint $table) {
            if (Schema::hasTable('product_variant_items')) {
                $table->foreign('variant_category_id')
                    ->references('id')->on('product_variant_categories')
                    ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variant_categories');
    }
};
