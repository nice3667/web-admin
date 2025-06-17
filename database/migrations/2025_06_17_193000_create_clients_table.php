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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('partner_account')->nullable();
            $table->string('client_uid')->unique();
            $table->string('client_id')->nullable();
            $table->date('reg_date')->nullable();
            $table->string('client_country')->nullable();
            $table->decimal('volume_lots', 20, 8)->default(0);
            $table->decimal('volume_mln_usd', 20, 8)->default(0);
            $table->decimal('reward_usd', 20, 8)->default(0);
            $table->string('client_status')->default('UNKNOWN');
            $table->boolean('kyc_passed')->default(false);
            $table->boolean('ftd_received')->default(false);
            $table->boolean('ftt_made')->default(false);
            $table->json('raw_data')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();

            // Add indexes for frequently searched fields
            $table->index('partner_account');
            $table->index('client_country');
            $table->index('client_status');
            $table->index('reg_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
