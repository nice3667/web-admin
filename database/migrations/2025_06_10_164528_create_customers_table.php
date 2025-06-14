<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('phone')->nullable();
            $table->string('broker')->nullable(); // ชื่อโบรกเกอร์ เช่น XM, Exness
            $table->decimal('deposit_amount', 15, 2)->default(0); // ยอดฝาก
            $table->decimal('withdraw_amount', 15, 2)->default(0); // ยอดถอน
            $table->decimal('lot_size', 10, 2)->default(0); // ปริมาณ Lot
            $table->timestamp('registered_at')->nullable(); // วันที่สมัคร
            $table->json('children')->nullable();
            $table->timestamps(); // created_at และ updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
