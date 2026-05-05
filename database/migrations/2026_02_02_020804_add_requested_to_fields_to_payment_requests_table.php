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
        Schema::table('payment_requests', function (Blueprint $table) {
            $table->foreignId('requested_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('requested_to_name')->nullable(); // For external people
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_requests', function (Blueprint $table) {
            $table->dropForeign(['requested_to_user_id']);
            $table->dropColumn(['requested_to_user_id', 'requested_to_name']);
        });
    }
};
