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
        Schema::table('product_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('product_movements', 'recipient_identifier')) {
                $table->string('recipient_identifier')->nullable()->after('recipient_name');
            }
            if (!Schema::hasColumn('product_movements', 'source_identifier')) {
                $table->string('source_identifier')->nullable()->after('source_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_movements', function (Blueprint $table) {
            $table->dropColumn(['recipient_identifier', 'source_identifier']);
        });
    }
};
