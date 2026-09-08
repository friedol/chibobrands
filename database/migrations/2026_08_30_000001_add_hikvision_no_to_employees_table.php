<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Stores the Hikvision device employeeNoString (e.g. "0001").
            // Set this per employee via HR admin after enrolling them on the device.
            $table->string('hikvision_no', 20)->nullable()->unique()->after('employee_code');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique(['hikvision_no']);
            $table->dropColumn('hikvision_no');
        });
    }
};
