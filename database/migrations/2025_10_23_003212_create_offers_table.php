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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('product_barcode'); // Reference to enhanced_products
            $table->string('offer_type'); // percentage, fixed, buy_x_get_y, bulk_discount
            $table->string('target_channel'); // retail, wholesale, both
            $table->decimal('discount_value', 10, 2); // The discount amount or percentage
            $table->decimal('min_quantity', 10, 2)->nullable(); // For bulk discounts
            $table->decimal('free_quantity', 10, 2)->nullable(); // For buy X get Y
            $table->date('start_date');
            $table->date('end_date');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('product_barcode');
            $table->index('is_active');
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
