<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_audit_trail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('action', 50);                 // created|updated|deleted|reconciled|adjusted|waived
            $table->string('entity_type', 80);            // payment|design_task|order|reconciliation|expense
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->text('reason');                       // always required
            $table->date('transaction_date')->nullable(); // historical date (separate from created_at)
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
            $table->index('action');
            $table->index('transaction_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_audit_trail');
    }
};
