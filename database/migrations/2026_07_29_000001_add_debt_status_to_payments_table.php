<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('debt_status', ['pending', 'partial', 'paid', 'reconciled', 'waived'])
                  ->default('pending')->after('is_debt')->nullable();
            $table->timestamp('reconciled_at')->nullable()->after('debt_status');
            $table->unsignedBigInteger('reconciled_by')->nullable()->after('reconciled_at');
            $table->text('reconciliation_note')->nullable()->after('reconciled_by');
        });

        // Backfill: mark non-debt payments as 'paid'
        DB::statement("UPDATE payments SET debt_status = 'paid' WHERE is_debt = 0 OR is_debt IS NULL");

        // Backfill debt payments: check if linked design_task has balance == 0
        DB::statement("
            UPDATE payments p
            LEFT JOIN design_tasks dt ON dt.id = p.design_task_id
            SET p.debt_status = CASE
                WHEN p.is_debt = 1 AND p.design_task_id IS NOT NULL AND dt.balance = 0 THEN 'paid'
                WHEN p.is_debt = 1 AND p.design_task_id IS NOT NULL AND dt.balance > 0  THEN 'pending'
                WHEN p.is_debt = 1 AND p.design_task_id IS NULL                         THEN 'pending'
                ELSE p.debt_status
            END
            WHERE p.is_debt = 1
        ");
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['debt_status', 'reconciled_at', 'reconciled_by', 'reconciliation_note']);
        });
    }
};
