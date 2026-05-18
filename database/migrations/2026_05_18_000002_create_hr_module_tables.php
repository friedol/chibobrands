<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Employees master data
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('employee_code')->unique();
            $table->string('full_name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('national_id')->nullable();
            $table->string('department')->nullable();
            $table->string('role_title')->nullable();
            $table->enum('contract_type', ['permanent', 'contract', 'part_time', 'intern'])->default('permanent');
            $table->date('hire_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('allowances', 15, 2)->default(0);
            $table->decimal('deductions', 15, 2)->default(0);
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->text('address')->nullable();
            $table->string('photo')->nullable();
            $table->enum('status', ['active', 'inactive', 'terminated', 'on_leave'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Attendance records
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->date('attendance_date');
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            $table->decimal('hours_worked', 5, 2)->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'on_leave', 'holiday'])->default('present');
            $table->boolean('is_late')->default(false);
            $table->integer('late_minutes')->default(0);
            $table->string('location')->nullable(); // GPS location or manual entry
            $table->string('method')->default('manual'); // manual, biometric, gps
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['employee_id', 'attendance_date']);
        });

        // Leave management
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('leave_type'); // annual, sick, unpaid, maternity, paternity, compassionate
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_days');
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Leave balances per employee per year
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->integer('year');
            $table->string('leave_type');
            $table->decimal('total_days', 5, 1)->default(0);
            $table->decimal('used_days', 5, 1)->default(0);
            $table->decimal('remaining_days', 5, 1)->default(0);
            $table->timestamps();
            $table->unique(['employee_id', 'year', 'leave_type']);
        });

        // Employee KPIs / evaluations
        Schema::create('employee_kpis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('evaluated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('period_type'); // daily, weekly, monthly
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('attendance_score', 5, 2)->default(0); // 0-100
            $table->decimal('productivity_score', 5, 2)->default(0); // 0-100
            $table->decimal('quality_score', 5, 2)->default(0); // 0-100
            $table->decimal('punctuality_score', 5, 2)->default(0); // 0-100
            $table->decimal('teamwork_score', 5, 2)->default(0); // 0-100
            $table->decimal('overall_score', 5, 2)->default(0); // calculated average
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('goals_next_period')->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();
        });

        // Add repeated_customer flag to customers
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('is_repeated')->default(false)->after('is_active');
            $table->date('first_purchase_date')->nullable()->after('is_repeated');
            $table->integer('purchase_count')->default(0)->after('first_purchase_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_kpis');
        Schema::dropIfExists('leave_balances');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('employees');

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['is_repeated', 'first_purchase_date', 'purchase_count']);
        });
    }
};
