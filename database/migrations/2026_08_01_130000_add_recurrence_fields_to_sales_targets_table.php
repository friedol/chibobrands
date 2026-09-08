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
        Schema::table('sales_targets', function (Blueprint $table) {
            $table->boolean('recurrence_enabled')->default(false)->after('end_date');
            $table->enum('recurrence_period', ['daily', 'weekly', 'monthly', 'yearly'])->nullable()->after('recurrence_enabled');
            $table->unsignedBigInteger('recurrence_source_id')->nullable()->after('recurrence_period');

            $table->index('recurrence_source_id');
            $table->index(['recurrence_enabled', 'recurrence_period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_targets', function (Blueprint $table) {
            $table->dropIndex(['recurrence_source_id']);
            $table->dropIndex(['recurrence_enabled', 'recurrence_period']);

            $table->dropColumn(['recurrence_enabled', 'recurrence_period', 'recurrence_source_id']);
        });
    }
};
