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
        if (Schema::hasTable('product_movements')) return;
        Schema::create('product_movements', function (Blueprint $table) {
            $table->id();
            
            // Core Movement Details
            $table->enum('type', ['in', 'out'])->index();
            $table->timestamp('movement_date')->useCurrent();
            $table->foreignId('gatekeeper_id')->constrained('users')->onDelete('restrict'); 
            
            // Product Details
            $table->string('product_name');
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2)->nullable();
            
            // Purpose & Auth
            $table->string('purpose'); // Delivery, Purchase, Return, Transfer, Repair, Official duty
            $table->string('authorization_reference')->nullable();
            
            // Handler Information (Who physically moved it)
            $table->string('handler_type'); // Registered Delivery, Staff, Customer, External Person
            $table->string('handler_name');
            $table->string('handler_identifier')->nullable(); // ID, Phone, etc.
            $table->foreignId('handler_user_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Incoming Specific (Source)
            $table->string('source_type')->nullable(); // Supplier, Customer return, Staff, External source
            $table->string('source_name')->nullable();
            
            // Outgoing Specific (Recipient & Method)
            $table->string('recipient_type')->nullable(); // Customer, Staff, External Party
            $table->string('recipient_name')->nullable();
            $table->string('delivery_method')->nullable(); // Company delivery, External delivery, Self-pickup
            
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes(); // For "Cannot be deleted" (we can use soft deletes or just policy, but soft deletes implies we can restore. "Cannot be deleted" usually means immutable for logic, but maybe soft delete for admin cleanup if mistake). 
            // The requirement says "Cannot be deleted", "Can only be edited with supervisor". 
            // I will use softDeletes just in case, but enforce policy in app.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_movements');
    }
};
