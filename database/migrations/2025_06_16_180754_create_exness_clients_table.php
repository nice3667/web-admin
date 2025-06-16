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
        Schema::create('exness_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exness_user_id')->constrained()->onDelete('cascade');
            $table->string('client_uid')->index();
            $table->string('client_name')->nullable();
            $table->string('client_email')->nullable();
            $table->string('client_status')->default('UNKNOWN');
            $table->decimal('volume_lots', 15, 4)->default(0);
            $table->decimal('volume_mln_usd', 15, 4)->default(0);
            $table->decimal('reward_usd', 15, 2)->default(0);
            $table->decimal('rebate_amount_usd', 15, 2)->default(0);
            $table->string('currency', 10)->default('USD');
            $table->date('reg_date')->nullable();
            $table->timestamp('last_activity')->nullable();
            $table->json('raw_data_v1')->nullable(); // Store raw V1 data
            $table->json('raw_data_v2')->nullable(); // Store raw V2 data
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['exness_user_id', 'client_status']);
            $table->index(['client_uid', 'exness_user_id']);
            $table->index('synced_at');
            $table->index('reg_date');
            
            // Unique constraint
            $table->unique(['exness_user_id', 'client_uid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exness_clients');
    }
};
