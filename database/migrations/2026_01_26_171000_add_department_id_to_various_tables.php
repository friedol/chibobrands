<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->constrained();
            $table->string('type')->default('sales_invoice'); // proforma, sales_invoice, receipt
        });

        Schema::table('design_tasks', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->constrained();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->constrained();
        });

        Schema::table('enhanced_products', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->constrained();
        });
    }

    public function down(): void
    {
        Schema::table('enhanced_products', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });

        Schema::table('design_tasks', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn(['department_id', 'type']);
        });
    }
};
