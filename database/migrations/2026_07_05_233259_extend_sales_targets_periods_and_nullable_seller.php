<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Widen the enum first so existing 'annual' rows remain valid while we migrate them.
        DB::statement("ALTER TABLE sales_targets MODIFY period ENUM('daily', 'weekly', 'monthly', 'quarterly', 'annual', 'yearly') NOT NULL");

        DB::table('sales_targets')->where('period', 'annual')->update(['period' => 'yearly']);

        // Drop the now-unused 'annual' value.
        DB::statement("ALTER TABLE sales_targets MODIFY period ENUM('daily', 'weekly', 'monthly', 'quarterly', 'yearly') NOT NULL");

        Schema::table('sales_targets', function (Blueprint $table) {
            $table->unsignedBigInteger('seller_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('sales_targets')->whereNull('seller_id')->delete();

        Schema::table('sales_targets', function (Blueprint $table) {
            $table->unsignedBigInteger('seller_id')->nullable(false)->change();
        });

        DB::statement("ALTER TABLE sales_targets MODIFY period ENUM('daily', 'weekly', 'monthly', 'quarterly', 'yearly', 'annual') NOT NULL");

        DB::table('sales_targets')->where('period', 'yearly')->update(['period' => 'annual']);

        DB::statement("ALTER TABLE sales_targets MODIFY period ENUM('monthly', 'quarterly', 'annual') NOT NULL");
    }
};
