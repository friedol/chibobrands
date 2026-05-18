<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->timestamp('welcome_sms_sent_at')->nullable()->after('purchase_count');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->timestamp('last_reminder_sms_at')->nullable()->after('days_overdue');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('welcome_sms_sent_at');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('last_reminder_sms_at');
        });
    }
};
