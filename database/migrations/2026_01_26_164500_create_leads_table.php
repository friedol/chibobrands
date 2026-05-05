<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('product_requested')->nullable();
            $table->string('source')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->foreignId('assigned_seller_id')->nullable()->constrained('users');
            $table->enum('status', ['pending', 'converted', 'not_interested'])->default('pending');
            $table->string('interest_level')->nullable();
            $table->text('customer_response')->nullable();
            $table->timestamps();
        });

        Schema::create('lead_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->text('notes');
            $table->date('follow_up_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_follow_ups');
        Schema::dropIfExists('leads');
    }
};
