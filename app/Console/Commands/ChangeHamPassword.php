<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ChangeHamPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:change-ham-password';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'เปลี่ยนรหัสผ่านของ Ham เป็น Tradewaen@2025';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔐 กำลังเปลี่ยนรหัสผ่านของ Ham...');

        // ค้นหาผู้ใช้ Ham
        $hamUser = User::where('email', 'hamsftmo@gmail.com')->first();

        if (!$hamUser) {
            $this->error('❌ ไม่พบผู้ใช้ Ham ในระบบ');
            $this->error('กรุณาตรวจสอบว่ามีผู้ใช้ที่มีอีเมล hamsftmo@gmail.com ในฐานข้อมูลหรือไม่');
            return 1;
        }

        $this->info("✅ พบผู้ใช้ Ham: {$hamUser->name} (ID: {$hamUser->id})");

        // เปลี่ยนรหัสผ่าน
        $newPassword = 'Tradewaen@2025';
        $hamUser->password = Hash::make($newPassword);
        $hamUser->save();

        $this->info("✅ เปลี่ยนรหัสผ่านของ Ham เป็น '{$newPassword}' เรียบร้อยแล้ว");
        $this->info("📧 อีเมล: {$hamUser->email}");
        $this->info("👤 ชื่อ: {$hamUser->name}");

        return 0;
    }
} 