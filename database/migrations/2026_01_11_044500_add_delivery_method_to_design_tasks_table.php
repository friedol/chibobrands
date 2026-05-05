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
        if (!Schema::hasColumn('design_tasks', 'delivery_method')) {
            Schema::table('design_tasks', function (Blueprint $table) {
                $table->string('delivery_method')->nullable()->after('delivery_status'); // 'pickup' or 'delivery'
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('design_tasks', function (Blueprint $table) {
            $table->dropColumn('delivery_method');
        });
    }
};
