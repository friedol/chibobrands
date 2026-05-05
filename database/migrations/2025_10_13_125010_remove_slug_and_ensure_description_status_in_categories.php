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
        Schema::table('categories', function (Blueprint $table) {
            // Remove slug column and its index if they exist
            if (Schema::hasColumn('categories', 'slug')) {
                // Drop unique index first
                $table->dropUnique(['slug']);
                // Drop the column
                $table->dropColumn('slug');
            }
            
            // Ensure description column exists
            if (!Schema::hasColumn('categories', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            
            // Ensure is_active (status) column exists
            if (!Schema::hasColumn('categories', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Add slug column back
            if (!Schema::hasColumn('categories', 'slug')) {
                $table->string('slug')->unique()->after('name');
            }
            
            // Note: We don't remove description and is_active as they might have been there before
        });
    }
};
