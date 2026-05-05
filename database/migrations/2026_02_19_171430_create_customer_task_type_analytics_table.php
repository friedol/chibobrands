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
        Schema::create('customer_task_type_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('design_task_type_id')->constrained()->onDelete('cascade');
            $table->integer('avg_reorder_interval')->nullable(); // in days
            $table->date('last_purchase_date')->nullable();
            $table->date('next_expected_purchase_date')->nullable();
            $table->integer('total_quantity_bought')->default(0);
            $table->timestamps();

            $table->unique(['customer_id', 'design_task_type_id'], 'cust_task_type_unique');
            $table->index('next_expected_purchase_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_task_type_analytics');
    }
};
