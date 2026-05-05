<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('product_movements', 'recipient_identifier')) {
                $table->string('recipient_identifier')->nullable()->after('recipient_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_movements', function (Blueprint $table) {
            if (Schema::hasColumn('product_movements', 'recipient_identifier')) {
                $table->dropColumn('recipient_identifier');
            }
        });
    }
};

