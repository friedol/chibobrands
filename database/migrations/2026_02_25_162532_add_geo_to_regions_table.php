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
        if (!Schema::hasTable('regions')) {
            return;
        }

        Schema::table('regions', function (Blueprint $table) {
            if (!Schema::hasColumn('regions', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable();
            }

            if (!Schema::hasColumn('regions', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('regions')) {
            return;
        }

        Schema::table('regions', function (Blueprint $table) {
            $dropColumns = [];

            if (Schema::hasColumn('regions', 'latitude')) {
                $dropColumns[] = 'latitude';
            }

            if (Schema::hasColumn('regions', 'longitude')) {
                $dropColumns[] = 'longitude';
            }

            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
