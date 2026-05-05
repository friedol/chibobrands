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
        Schema::table('enhanced_products', function (Blueprint $table) {
            $table->string('product_nickname')->nullable()->after('name');
            $table->string('slug')->nullable()->after('product_nickname');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enhanced_products', function (Blueprint $table) {
            $table->dropColumn(['product_nickname', 'slug']);
        });
    }
};
