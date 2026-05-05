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
        if (!Schema::hasColumn('design_tasks', 'delivery_id')) {
            Schema::table('design_tasks', function (Blueprint $table) {
                $table->unsignedBigInteger('delivery_id')->nullable()->after('operator_id');
                $table->string('delivery_status')->nullable()->after('delivery_id');
                $table->text('delivery_notes')->nullable()->after('delivery_status');
                $table->timestamp('delivered_at')->nullable()->after('delivery_notes');
                
                $table->foreign('delivery_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('design_tasks', function (Blueprint $table) {
            $table->dropForeign(['delivery_id']);
            $table->dropColumn(['delivery_id', 'delivery_status', 'delivery_notes', 'delivered_at']);
        });
    }
};
