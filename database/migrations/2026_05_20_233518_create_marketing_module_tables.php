<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('marketing_theme_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('objective')->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->string('target_audience')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->string('status')->default('planning');
            $table->text('expected_result')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('stage')->nullable(); // awareness, consideration, conversion
            $table->text('objective')->nullable();
            $table->decimal('budget', 10, 2)->default(0);
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->string('target_market')->nullable();
            $table->integer('expected_reach')->default(0);
            $table->integer('actual_reach')->default(0);
            $table->string('status')->default('draft');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('campaign_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->string('activity_name');
            $table->string('platform')->nullable();
            $table->string('content_type')->nullable(); // poster, video, picture
            $table->dateTime('scheduled_at')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
            
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
        });

        Schema::create('marketing_calendar_events', function (Blueprint $table) {
            $table->id();
            $table->string('source_type'); // App\Models\Campaign, App\Models\MarketingThemeEvent, App\Models\Ad
            $table->unsignedBigInteger('source_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_datetime')->nullable();
            $table->dateTime('end_datetime')->nullable();
            $table->string('platform')->nullable();
            $table->string('content_format')->nullable();
            $table->text('objective')->nullable();
            $table->string('segment')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->string('status')->default('scheduled');
            $table->timestamps();
            
            $table->index(['source_type', 'source_id']);
        });

        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id')->nullable();
            $table->string('ad_name');
            $table->string('platform')->nullable();
            $table->text('objective')->nullable();
            $table->string('target_audience')->nullable();
            $table->decimal('budget', 10, 2)->default(0);
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->string('content_type')->nullable();
            $table->string('status')->default('draft');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('set null');
        });

        Schema::create('ad_performance_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ad_id');
            $table->integer('leads')->default(0);
            $table->integer('conversions')->default(0);
            $table->integer('saves')->default(0);
            $table->integer('shares')->default(0);
            $table->integer('impressions')->default(0);
            $table->integer('engagement')->default(0);
            $table->decimal('amount_spent', 10, 2)->default(0);
            $table->decimal('cpr', 10, 2)->default(0); // cost per result
            $table->decimal('roi', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->date('report_date');
            $table->timestamps();
            
            $table->foreign('ad_id')->references('id')->on('ads')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_performance_reports');
        Schema::dropIfExists('ads');
        Schema::dropIfExists('marketing_calendar_events');
        Schema::dropIfExists('campaign_activities');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('marketing_theme_events');
    }
};
