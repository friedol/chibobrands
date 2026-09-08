<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reconciled_by')->index();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->unsignedBigInteger('payment_id')->nullable()->index();
            $table->unsignedBigInteger('design_task_id')->nullable()->index();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->decimal('amount', 15, 2);
            $table->date('transaction_date');             // historical date of the original transaction
            $table->date('reconciliation_date');          // date this reconciliation was recorded
            $table->enum('type', [
                'debt_write_off',
                'partial_payment',
                'full_payment',
                'credit_note',
                'adjustment',
                'historical_entry',
            ])->default('historical_entry');
            $table->enum('status', ['pending_review', 'approved', 'rejected'])->default('approved');
            $table->string('reference')->nullable();      // cheque/transfer/receipt number
            $table->text('notes')->nullable();
            $table->text('reason');                       // why this reconciliation was needed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_reconciliations');
    }
};
