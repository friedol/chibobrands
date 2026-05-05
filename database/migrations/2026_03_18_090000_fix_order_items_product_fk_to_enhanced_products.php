<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'product_id')) {
                // Only drop existing FK if it exists.
                // Adding a new FK fails because existing rows might not match.
                $fkExists = DB::table('information_schema.TABLE_CONSTRAINTS')
                    ->where('CONSTRAINT_NAME', 'order_items_product_id_foreign')
                    ->where('TABLE_NAME', 'order_items')
                    ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
                    ->exists();

                if ($fkExists) {
                    $table->dropForeign('order_items_product_id_foreign');
                }
            }
        });
    }

    public function down(): void
    {
        // Intentionally left as no-op to avoid re-adding an FK that can fail
        // due to existing inconsistent data.
    }
};

