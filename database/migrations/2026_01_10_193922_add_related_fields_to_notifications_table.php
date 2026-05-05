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
        if (!Schema::hasColumn('notifications', 'related_id')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->unsignedBigInteger('related_id')->nullable()->after('message');
                $table->string('related_type')->nullable()->after('related_id');
                $table->index(['related_type', 'related_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['related_type', 'related_id']);
            $table->dropColumn(['related_id', 'related_type']);
        });
    }
};
