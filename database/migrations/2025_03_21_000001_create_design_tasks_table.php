<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('design_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('receptionist_id')->constrained('admins')->onDelete('cascade');
            $table->foreignId('designer_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->enum('status', ['pending', 'in_progress', 'in_review', 'completed', 'rejected'])->default('pending');
            $table->integer('priority')->default(1); // 1-5, 1 being highest
            $table->dateTime('deadline')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('design_tasks');
    }
};
