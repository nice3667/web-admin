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
        Schema::create('kantapong_clients', function (Blueprint $table) {
            $table->id();
            $table->string('client_uid')->unique();
            $table->string('partner_account')->nullable();
            $table->string('client_country')->nullable();
            $table->timestamp('reg_date')->nullable();
            $table->decimal('volume_lots', 15, 4)->default(0);
            $table->decimal('volume_mln_usd', 15, 4)->default(0);
            $table->decimal('reward_usd', 15, 4)->default(0);
            $table->decimal('rebate_amount_usd', 15, 4)->default(0);
            $table->boolean('kyc_passed')->default(false);
            $table->boolean('ftd_received')->default(false);
            $table->boolean('ftt_made')->default(false);
            $table->timestamps();
            
            $table->index('client_uid');
            $table->index('partner_account');
            $table->index('reg_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kantapong_clients');
    }
}; 