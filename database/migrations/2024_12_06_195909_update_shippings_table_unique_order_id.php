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
        Schema::create('shippings', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('order_id')->unique(); // Unique Foreign key to orders
            $table->string('name'); // Recipient's name
            $table->string('address'); // Street address
            $table->string('city'); // City
            $table->string('state')->nullable(); // State or province
            $table->string('postal_code'); // ZIP/Postal code
            $table->string('country'); // Country
            $table->string('phone')->nullable(); // Contact phone
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
        Schema::dropIfExists('shippings');
    }
};