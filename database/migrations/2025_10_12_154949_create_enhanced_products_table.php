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
        Schema::create('enhanced_products', function (Blueprint $table) {
            $table->id();
            $table->string('product_id')->unique();
            $table->string('name');
            $table->string('barcode')->unique();
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('measured_in')->default('pieces');
            $table->string('brand')->nullable();
            $table->string('material')->nullable();
            $table->string('printing_type')->nullable();
            $table->decimal('buying_price', 10, 2)->nullable();
            $table->boolean('retail_visible')->default(true);
            $table->boolean('wholesale_visible')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enhanced_products');
    }
};
