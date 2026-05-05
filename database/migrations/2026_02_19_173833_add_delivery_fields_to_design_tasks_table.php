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
        Schema::table('design_tasks', function (Blueprint $table) {
            $table->decimal('delivery_cost', 15, 2)->default(0)->after('amount_paid');
            $table->decimal('delivery_discount', 15, 2)->default(0)->after('delivery_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('design_tasks', function (Blueprint $table) {
            $table->dropColumn(['delivery_cost', 'delivery_discount']);
        });
    }
};
