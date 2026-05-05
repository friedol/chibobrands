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
        Schema::create('product_variant_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variant_category_id');
            $table->index('variant_category_id');
            $table->string('name'); // e.g., Red, Large
            $table->string('color_code', 7)->nullable(); // #RRGGBB for Color category
            $table->string('description')->nullable();
            $table->decimal('price', 12, 2)->default(0); // per-option additional price
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variant_items');
    }
};
