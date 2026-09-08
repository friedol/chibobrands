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
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'registered_by_id')) {
                $table->foreignId('registered_by_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('customers', 'account_owner_id')) {
                $table->foreignId('account_owner_id')->nullable()->after('registered_by_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('customers', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('account_owner_id');
            }
            if (!Schema::hasColumn('customers', 'customer_source')) {
                $table->string('customer_source')->nullable()->after('business_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['registered_by_id']);
            $table->dropForeign(['account_owner_id']);
            $table->dropColumn(['registered_by_id', 'account_owner_id', 'branch_id', 'customer_source']);
        });
    }
};
