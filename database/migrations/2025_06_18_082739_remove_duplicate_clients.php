<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove duplicate clients, keeping only the latest record for each client_uid
        DB::statement("
            DELETE c1 FROM clients c1
            INNER JOIN clients c2 
            WHERE c1.id > c2.id 
            AND c1.client_uid = c2.client_uid
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot restore deleted duplicates
    }
};
