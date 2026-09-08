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
        if (!Schema::hasColumn('design_tasks', 'customer_business_id')) {
            Schema::table('design_tasks', function (Blueprint $table) {
                $table->foreignId('customer_business_id')
                    ->nullable()
                    ->after('customer_id')
                    ->constrained('customer_businesses')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('orders', 'customer_business_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreignId('customer_business_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('customer_businesses')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('payments', 'customer_business_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->foreignId('customer_business_id')
                    ->nullable()
                    ->after('customer_id')
                    ->constrained('customer_businesses')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('payments', 'customer_business_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropForeign(['customer_business_id']);
                $table->dropColumn('customer_business_id');
            });
        }

        if (Schema::hasColumn('orders', 'customer_business_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropForeign(['customer_business_id']);
                $table->dropColumn('customer_business_id');
            });
        }

        if (Schema::hasColumn('design_tasks', 'customer_business_id')) {
            Schema::table('design_tasks', function (Blueprint $table) {
                $table->dropForeign(['customer_business_id']);
                $table->dropColumn('customer_business_id');
            });
        }
    }
};
