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
        // Add operator to the role enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'super_admin', 'manager', 'saler', 'receptionist', 'designer', 'operator', 'retail_customer', 'wholesale_customer') DEFAULT 'retail_customer'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove operator from the enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'super_admin', 'manager', 'saler', 'receptionist', 'designer', 'retail_customer', 'wholesale_customer') DEFAULT 'retail_customer'");
    }
};
