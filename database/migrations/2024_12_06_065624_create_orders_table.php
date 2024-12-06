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
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('user_id'); // Foreign key to users
            $table->string('order_number')->unique(); // Unique order identifier
            $table->decimal('subtotal', 10, 2); // Subtotal before tax and discounts
            $table->decimal('tax', 10, 2)->default(0); // Tax amount
            $table->decimal('discount', 10, 2)->default(0); // Discount applied
            $table->decimal('total', 10, 2); // Total after tax and discounts
            $table->string('currency', 10)->default('USD'); // Currency code
            $table->enum('status', ['pending', 'paid', 'shipped', 'cancelled', 'refunded'])->default('pending'); // Order status
            $table->timestamps(); // Created and updated timestamps
            $table->softDeletes(); // Allow soft deletes (optional)
        
            // Relationships
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
