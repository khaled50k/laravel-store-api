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
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Foreign key to users
            $table->string('address_line_1'); // Street address
            $table->string('address_line_2')->nullable(); // Apartment, suite, etc.
            $table->string('city'); // City
            $table->string('state')->nullable(); // State/Province
            $table->string('postal_code'); // ZIP/Postal code
            $table->string('country'); // Country
            $table->enum('type', ['shipping', 'billing'])->default('shipping'); // Address type
            $table->boolean('is_default')->default(false); // Default address indicator
            $table->timestamps(); // Created at & updated at timestamps
        
            // Relationships
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};