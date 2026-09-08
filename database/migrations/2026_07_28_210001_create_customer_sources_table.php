<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('customer_sources')) {
            Schema::create('customer_sources', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });

            // Seed default customer sources
            $sources = [
                ['name' => 'WhatsApp', 'sort_order' => 1],
                ['name' => 'Instagram', 'sort_order' => 2],
                ['name' => 'Facebook', 'sort_order' => 3],
                ['name' => 'TikTok', 'sort_order' => 4],
                ['name' => 'Website', 'sort_order' => 5],
                ['name' => 'Referral', 'sort_order' => 6],
                ['name' => 'Walk-in', 'sort_order' => 7],
                ['name' => 'Google Search', 'sort_order' => 8],
                ['name' => 'LinkedIn', 'sort_order' => 9],
                ['name' => 'Exhibition', 'sort_order' => 10],
                ['name' => 'Livaro', 'sort_order' => 11],
                ['name' => 'Other', 'sort_order' => 12],
            ];

            foreach ($sources as $source) {
                DB::table('customer_sources')->insert(array_merge($source, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_sources');
    }
};
