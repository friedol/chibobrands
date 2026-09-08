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
        if (!Schema::hasColumn('leads', 'customer_id')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->foreignId('customer_id')->nullable()->after('id')->constrained('customers')->nullOnDelete();
                $table->timestamp('converted_at')->nullable()->after('status');
            });
        }

        if (!Schema::hasColumn('customers', 'country')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('country', 100)->default('Tanzania')->after('address');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('leads', 'customer_id')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropForeign(['customer_id']);
                $table->dropColumn(['customer_id', 'converted_at']);
            });
        }

        if (Schema::hasColumn('customers', 'country')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('country');
            });
        }
    }
};
