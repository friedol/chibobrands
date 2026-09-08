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
        // Add marketing_manager and hr_officer to the role enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'super_admin', 'manager', 'saler', 'receptionist', 'designer', 'operator', 'delivery', 'gatekeeper', 'retail_customer', 'wholesale_customer', 'accountant', 'marketing_manager', 'hr_officer') DEFAULT 'retail_customer'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback to previous roles
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'super_admin', 'manager', 'saler', 'receptionist', 'designer', 'operator', 'delivery', 'gatekeeper', 'retail_customer', 'wholesale_customer', 'accountant') DEFAULT 'retail_customer'");
    }
};
