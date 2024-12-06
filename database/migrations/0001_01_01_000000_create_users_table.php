<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Auto-increment primary key
            $table->string('first_name'); // User's first name
            $table->string('last_name'); // User's last name
            $table->string('email')->unique(); // Unique email address
            $table->string('phone')->nullable(); // Phone number (optional)
            $table->string('password'); // Encrypted password
            $table->string('avatar')->nullable(); // Profile picture URL (optional)
            $table->enum('role', ['customer', 'admin'])->default('customer'); // User role
            $table->boolean('is_active')->default(true); // User active status
            $table->timestamp('email_verified_at')->nullable(); // Email verification timestamp
            $table->rememberToken(); // Token for "Remember Me" functionality
            $table->timestamps(); // Created at & updated at timestamps
            $table->softDeletes(); // Soft delete (optional)
        });


        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
