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
        // Step 1: Backup existing category data
        $existingCategories = DB::table('categories')
            ->select('id', 'name', 'description', 'is_active')
            ->get()
            ->toArray();
        
        // Step 2: Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Step 3: Drop the old categories table
        Schema::dropIfExists('categories');
        
        // Step 3: Create new clean categories table with only essential columns
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes for performance
            $table->index('name');
            $table->index('is_active');
        });
        
        // Step 4: Restore the backed up data
        foreach ($existingCategories as $category) {
            DB::table('categories')->insert([
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'is_active' => $category->is_active,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // Step 5: Reset auto-increment to continue from the last ID
        if (count($existingCategories) > 0) {
            $maxId = max(array_column($existingCategories, 'id'));
            DB::statement("ALTER TABLE categories AUTO_INCREMENT = " . ($maxId + 1));
        }
        
        // Step 6: Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Backup data before reverting
        $categories = DB::table('categories')->get()->toArray();
        
        // Drop the new table
        Schema::dropIfExists('categories');
        
        // Recreate the old structure (with all columns)
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('image')->nullable();
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index('name');
            $table->index('is_active');
            $table->index('sort_order');
            $table->index('parent_id');
        });
        
        // Restore data
        foreach ($categories as $category) {
            DB::table('categories')->insert([
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'is_active' => $category->is_active,
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
            ]);
        }
    }
};
