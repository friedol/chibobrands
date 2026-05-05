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
        Schema::table('hero_slides', function (Blueprint $table) {
            // Ad-specific fields
            $table->boolean('is_ad')->default(false)->after('is_active');
            $table->string('ad_type')->nullable()->after('is_ad'); // 'banner', 'popup', 'sidebar', 'inline'
            $table->string('ad_position')->nullable()->after('ad_type'); // 'top', 'bottom', 'left', 'right', 'center'
            $table->integer('ad_duration')->nullable()->after('ad_position'); // Duration in seconds for auto-close
            $table->boolean('ad_closable')->default(true)->after('ad_duration'); // Can user close the ad
            $table->string('ad_target_audience')->nullable()->after('ad_closable'); // 'all', 'retail', 'wholesale', 'new_customers', 'returning_customers'
            $table->date('ad_start_date')->nullable()->after('ad_target_audience');
            $table->date('ad_end_date')->nullable()->after('ad_start_date');
            $table->integer('ad_click_count')->default(0)->after('ad_end_date');
            $table->integer('ad_impression_count')->default(0)->after('ad_click_count');
            $table->decimal('ad_budget', 10, 2)->nullable()->after('ad_impression_count');
            $table->decimal('ad_cost_per_click', 8, 2)->nullable()->after('ad_budget');
            $table->decimal('ad_cost_per_impression', 8, 2)->nullable()->after('ad_cost_per_click');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hero_slides', function (Blueprint $table) {
            $table->dropColumn([
                'is_ad',
                'ad_type',
                'ad_position',
                'ad_duration',
                'ad_closable',
                'ad_target_audience',
                'ad_start_date',
                'ad_end_date',
                'ad_click_count',
                'ad_impression_count',
                'ad_budget',
                'ad_cost_per_click',
                'ad_cost_per_impression'
            ]);
        });
    }
};