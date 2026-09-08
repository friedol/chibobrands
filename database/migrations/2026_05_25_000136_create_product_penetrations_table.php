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
        Schema::create('product_penetrations', function (Blueprint $table) {
            $table->id();
            $table->string('item_type')->default('product'); // 'product' or 'task_type'
            $table->unsignedBigInteger('product_id')->nullable();   // enhanced_products.id
            $table->unsignedBigInteger('task_type_id')->nullable();  // design_task_types.id
            $table->string('target_segment')->nullable();
            $table->decimal('current_penetration', 5, 2)->default(0);
            $table->decimal('target_penetration', 5, 2)->default(0);
            $table->text('strategy_notes')->nullable();
            $table->string('status')->default('planning');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_penetrations');
    }
};
