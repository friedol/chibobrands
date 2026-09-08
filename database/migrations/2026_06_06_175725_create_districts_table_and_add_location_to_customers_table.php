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
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained()->onDelete('cascade');
            $table->string('district_name');
            $table->timestamps();

            $table->unique(['region_id', 'district_name']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('region_id')->nullable()->after('address')->constrained()->onDelete('set null');
            $table->foreignId('district_id')->nullable()->after('region_id')->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['district_id']);
            $table->dropForeign(['region_id']);
            $table->dropColumn(['district_id', 'region_id']);
        });

        Schema::dropIfExists('districts');
    }
};
