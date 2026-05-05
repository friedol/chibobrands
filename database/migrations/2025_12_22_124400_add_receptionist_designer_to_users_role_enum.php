<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add receptionist and designer to the role enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'super_admin', 'manager', 'saler', 'receptionist', 'designer', 'retail_customer', 'wholesale_customer') DEFAULT 'retail_customer'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove receptionist and designer from the enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'super_admin', 'manager', 'saler', 'retail_customer', 'wholesale_customer') DEFAULT 'retail_customer'");
    }
};








