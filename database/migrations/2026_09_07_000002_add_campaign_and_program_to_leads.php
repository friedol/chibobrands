<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('campaign_id')->nullable()->after('source')
                  ->constrained('campaigns')->nullOnDelete();
            $table->foreignId('program_id')->nullable()->after('campaign_id')
                  ->constrained('sales_programs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['campaign_id']);
            $table->dropForeign(['program_id']);
            $table->dropColumn(['campaign_id', 'program_id']);
        });
    }
};
