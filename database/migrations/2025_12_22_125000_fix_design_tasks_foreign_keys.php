<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Check if the foreign key constraints exist and drop them
        Schema::table('design_tasks', function (Blueprint $table) {
            // Drop existing foreign keys if they exist
            $table->dropForeign(['receptionist_id']);
            $table->dropForeign(['designer_id']);
        });
        
        // Recreate with correct table reference
        Schema::table('design_tasks', function (Blueprint $table) {
            $table->foreign('receptionist_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('designer_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('design_tasks', function (Blueprint $table) {
            $table->dropForeign(['receptionist_id']);
            $table->dropForeign(['designer_id']);
        });
        
        Schema::table('design_tasks', function (Blueprint $table) {
            $table->foreign('receptionist_id')->references('id')->on('admins')->onDelete('cascade');
            $table->foreign('designer_id')->references('id')->on('admins')->onDelete('set null');
        });
    }
};








