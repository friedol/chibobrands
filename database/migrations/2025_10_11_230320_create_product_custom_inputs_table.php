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
        Schema::create('product_custom_inputs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('input_label');
            $table->enum('input_type', ['text', 'textarea', 'file']);
            $table->boolean('is_required')->default(false);
            $table->text('placeholder')->nullable();
            $table->text('validation_rules')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Foreign key constraint for product
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            
            // Indexes
            $table->index('product_id');
            $table->index('input_type');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_custom_inputs');
    }
};
