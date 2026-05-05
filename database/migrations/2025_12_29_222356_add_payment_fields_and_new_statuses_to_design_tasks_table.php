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
        Schema::table('design_tasks', function (Blueprint $row) {
            $row->decimal('amount_paid', 15, 2)->default(0)->after('price');
            $row->decimal('balance', 15, 2)->default(0)->after('amount_paid');
            // We'll handle statuses in the code, but we might want to ensure the column can take the new values if it's an enum (unlikely in this codebase)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('design_tasks', function (Blueprint $row) {
            $row->dropColumn(['amount_paid', 'balance']);
        });
    }
};
