<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('priority')->default('normal')->after('source'); // low, normal, high, urgent
            $table->decimal('promised_amount', 15, 2)->nullable()->after('priority');
            $table->date('promised_order_date')->nullable()->after('promised_amount');
            $table->string('lead_type')->default('new')->after('promised_order_date'); // new, follow_up, referral, repeat
            $table->text('last_follow_up_notes')->nullable()->after('lead_type');
            $table->date('last_follow_up_date')->nullable()->after('last_follow_up_notes');
            $table->integer('days_overdue')->default(0)->after('last_follow_up_date');
        });

        Schema::table('lead_follow_ups', function (Blueprint $table) {
            $table->enum('status', ['pending', 'completed', 'rescheduled'])->default('completed')->after('follow_up_date');
            $table->string('call_outcome')->nullable()->after('status'); // interested, not_interested, no_answer, callback_requested
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['priority', 'promised_amount', 'promised_order_date', 'lead_type', 'last_follow_up_notes', 'last_follow_up_date', 'days_overdue']);
        });

        Schema::table('lead_follow_ups', function (Blueprint $table) {
            $table->dropColumn(['status', 'call_outcome']);
        });
    }
};
