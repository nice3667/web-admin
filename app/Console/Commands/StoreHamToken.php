<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\ExnessUser;
use App\Services\HamExnessAuthService;
use Illuminate\Support\Facades\DB;

class StoreHamToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ham:store-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'เก็บ token ของ Ham ในฐานข้อมูล';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔐 กำลังเก็บ token ของ Ham ในฐานข้อมูล...');

        // ค้นหาผู้ใช้ Ham
        $hamUser = User::where('email', 'hamsftmo@gmail.com')->first();

        if (!$hamUser) {
            $this->error('❌ ไม่พบผู้ใช้ Ham ในระบบ');
            return 1;
        }

        $this->info("✅ พบผู้ใช้ Ham: {$hamUser->name} (ID: {$hamUser->id})");

        // รับ token จาก API
        $hamService = new HamExnessAuthService();
        $token = $hamService->retrieveToken();

        if (!$token) {
            $this->error('❌ ไม่สามารถรับ token จาก API ได้');
            return 1;
        }

        $this->info("✅ ได้รับ token จาก API แล้ว (ความยาว: " . strlen($token) . " ตัวอักษร)");

        // ตรวจสอบหรือสร้าง ExnessUser
        $exnessUser = ExnessUser::where('user_id', $hamUser->id)->first();

        if (!$exnessUser) {
            $this->info('📝 สร้าง ExnessUser ใหม่...');
            
            $exnessUser = new ExnessUser();
            $exnessUser->user_id = $hamUser->id;
            $exnessUser->exness_email = 'hamsftmo@gmail.com';
            $exnessUser->exness_password_encrypted = encrypt('Tradewaen@2025');
            $exnessUser->is_active = true;
        } else {
            $this->info('📝 อัปเดต ExnessUser ที่มีอยู่...');
        }

        // อัปเดตข้อมูล token
        $exnessUser->access_token = $token;
        $exnessUser->token_expires_at = now()->addHours(23); // Token หมดอายุใน 23 ชั่วโมง
        $exnessUser->last_sync_at = now();
        $exnessUser->save();

        $this->info("✅ เก็บ token ในฐานข้อมูลเรียบร้อยแล้ว");
        $this->info("📊 ExnessUser ID: {$exnessUser->id}");
        $this->info("⏰ Token หมดอายุ: {$exnessUser->token_expires_at}");
        $this->info("🔄 ซิงค์ล่าสุด: {$exnessUser->last_sync_at}");

        // ทดสอบการใช้งาน token
        $this->info("\n🧪 ทดสอบการใช้งาน token...");
        
        try {
            $clientsData = $hamService->getClientsData();
            
            if (isset($clientsData['error'])) {
                $this->warn("⚠️ เกิดข้อผิดพลาดในการดึงข้อมูล: {$clientsData['error']}");
            } else {
                $clientCount = isset($clientsData['data']) ? count($clientsData['data']) : 0;
                $this->info("✅ ดึงข้อมูลลูกค้าได้สำเร็จ: {$clientCount} รายการ");
            }
        } catch (\Exception $e) {
            $this->error("❌ เกิดข้อผิดพลาดในการทดสอบ: " . $e->getMessage());
        }

        return 0;
    }
} 