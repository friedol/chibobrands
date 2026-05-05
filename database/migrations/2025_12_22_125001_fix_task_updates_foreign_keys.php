<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('task_updates', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
        });
        
        Schema::table('task_updates', function (Blueprint $table) {
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('task_updates', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
        });
        
        Schema::table('task_updates', function (Blueprint $table) {
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
        });
    }
};








