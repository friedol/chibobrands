<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'cancelled' and 'delivered' to the status enum
        DB::statement("ALTER TABLE design_tasks MODIFY COLUMN status ENUM('pending', 'in_progress', 'in_review', 'completed', 'confirmed', 'printing', 'printed', 'super_completed', 'delivered', 'rejected', 'cancelled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */

    public function down(): void
    {
        // Revert to the previous known states (excluding cancelled and delivered)
        DB::statement("ALTER TABLE design_tasks MODIFY COLUMN status ENUM('pending', 'in_progress', 'in_review', 'completed', 'confirmed', 'printing', 'printed', 'super_completed', 'rejected') DEFAULT 'pending'");
    }
};
