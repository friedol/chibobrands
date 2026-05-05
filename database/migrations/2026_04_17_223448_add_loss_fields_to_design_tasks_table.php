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
            $table->boolean('is_loss')->default(false)->after('status');
            $table->text('loss_reason')->nullable()->after('is_loss');
            $table->timestamp('loss_recorded_at')->nullable()->after('loss_reason');
            $table->unsignedBigInteger('loss_recorded_by')->nullable()->after('loss_recorded_at');
            $table->decimal('loss_amount', 15, 2)->default(0)->after('loss_recorded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('design_tasks', function (Blueprint $table) {
            $table->dropColumn(['is_loss', 'loss_reason', 'loss_recorded_at', 'loss_recorded_by', 'loss_amount']);
        });
    }
};
