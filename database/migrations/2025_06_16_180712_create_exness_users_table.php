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
        Schema::create('exness_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('exness_email');
            $table->text('exness_password_encrypted');
            $table->text('access_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->json('api_response_v1')->nullable(); // Store last V1 API response
            $table->json('api_response_v2')->nullable(); // Store last V2 API response
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->unique('user_id');
            $table->index(['exness_email', 'is_active']);
            $table->index('token_expires_at');
            $table->index('last_sync_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exness_users');
    }
};
