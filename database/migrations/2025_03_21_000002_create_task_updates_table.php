<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('task_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('design_tasks')->onDelete('cascade');
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');
            $table->enum('type', ['status_update', 'comment', 'file_upload', 'revision']);
            $table->text('content');
            $table->json('metadata')->nullable(); // For storing file paths, status changes, etc.
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('task_updates');
    }
};
