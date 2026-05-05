<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 15, 2);
            $table->string('category');
            $table->foreignId('department_id')->constrained();
            $table->date('date');
            $table->foreignId('approved_by_id')->constrained('users');
            $table->string('payment_method'); // cash, mobile_money, bank
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
