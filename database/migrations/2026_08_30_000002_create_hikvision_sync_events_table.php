<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hikvision_sync_events', function (Blueprint $table) {
            $table->id();

            // Device that sent the event
            $table->string('device_id', 50);

            // Hikvision event serial — unique per device, used for idempotency
            $table->unsignedBigInteger('serial_no');

            // Raw Hikvision employee number string (e.g. "0001")
            $table->string('employee_no', 50);

            // Resolved Laravel employee (null if lookup failed)
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();

            $table->timestamp('event_time');
            $table->unsignedSmallInteger('major');
            $table->unsignedSmallInteger('minor');
            $table->string('verify_mode', 100);
            $table->unsignedTinyInteger('door_no');
            $table->unsignedTinyInteger('card_reader_no');

            // Processing outcome
            $table->enum('status', ['processed', 'duplicate', 'ignored', 'failed'])->default('failed');

            // The attendance record created/updated by this event
            $table->foreignId('attendance_id')->nullable()->constrained('attendances')->nullOnDelete();

            // Reason for ignored/failed status
            $table->text('failure_reason')->nullable();

            $table->timestamps();

            // Idempotency: same device + serial must not be processed twice
            $table->unique(['device_id', 'serial_no']);

            $table->index('employee_id');
            $table->index('status');
            $table->index('event_time');
        });

        // Add a back-reference on attendances so the UI can show Hikvision source info
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('hikvision_event_id')
                ->nullable()
                ->constrained('hikvision_sync_events')
                ->nullOnDelete()
                ->after('recorded_by');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['hikvision_event_id']);
            $table->dropColumn('hikvision_event_id');
        });

        Schema::dropIfExists('hikvision_sync_events');
    }
};
