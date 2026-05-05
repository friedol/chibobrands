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
        Schema::table('design_tasks', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->after('description');
            $table->foreignId('saler_id')->nullable()->after('designer_id')->constrained('users')->onDelete('set null');
            $table->boolean('requires_receipt')->default(false)->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('design_tasks', function (Blueprint $table) {
            $table->dropForeign(['saler_id']);
            $table->dropColumn(['price', 'saler_id', 'requires_receipt']);
        });
    }
};
