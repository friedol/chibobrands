<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Department;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $departments = [
            'MIFUKO',
            'CHIBO- MAIN',
            'CHIBO – SIGNAGE',
            'CHIBO- PROMO TECH',
            'GENERAL'
        ];

        foreach ($departments as $name) {
            Department::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Avoid deleting standard departments to prevent foreign key errors with associated tables.
    }
};
