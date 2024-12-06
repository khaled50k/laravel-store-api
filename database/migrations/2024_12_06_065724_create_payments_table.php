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
        Schema::create('payments', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('order_id'); // Foreign key to orders
            $table->string('payment_gateway')->default('paypal'); // Payment method (e.g., PayPal, Stripe)
            $table->string('transaction_id')->nullable(); // Payment gateway transaction ID
            $table->decimal('amount', 10, 2); // Payment amount
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending'); // Payment status
            $table->timestamps(); // Created and updated timestamps
        
            // Relationships
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
