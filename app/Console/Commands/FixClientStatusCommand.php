<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Client;
use Illuminate\Support\Facades\Log;

class FixClientStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clients:fix-status {--show-only : Show status without updating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix client_status for specific users to match raw_data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 ตรวจสอบและแก้ไข client_status ของผู้ใช้');
        $this->info('==========================================');

        // ตรวจสอบผู้ใช้ทั้ง 3 คน
        $emails = [
            'hamsftmo@gmail.com',
            'Janischa.trade@gmail.com', 

        ];

        $showOnly = $this->option('show-only');
        $totalUpdated = 0;

        foreach ($emails as $email) {
            $this->info("\n📧 ตรวจสอบผู้ใช้: {$email}");
            
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                $this->error("   ❌ ไม่พบผู้ใช้");
                continue;
            }
            
            $this->info("   ✅ พบผู้ใช้: {$user->name} (ID: {$user->id})");
            
            // ตรวจสอบข้อมูลลูกค้าที่เกี่ยวข้อง
            $clients = Client::where('partner_account', $user->id)->get();
            
            if ($clients->isEmpty()) {
                $this->warn("   ⚠️  ไม่พบข้อมูลลูกค้าที่เกี่ยวข้อง");
                
                // ลองค้นหาด้วย partner_account ที่เป็น string
                $clients = Client::where('partner_account', (string)$user->id)->get();
                
                if ($clients->isEmpty()) {
                    $this->error("   ❌ ไม่พบข้อมูลลูกค้าในรูปแบบใดๆ");
                    continue;
                }
            }
            
            $this->info("   📊 พบลูกค้า {$clients->count()} รายการ");
            
            // แสดงสถิติ client_status ปัจจุบัน
            $statusCounts = $clients->groupBy('client_status')->map->count();
            $this->info("   📈 สถิติ client_status ปัจจุบัน:");
            foreach ($statusCounts as $status => $count) {
                $this->line("      - {$status}: {$count} รายการ");
            }
            
            // ตรวจสอบข้อมูลจาก raw_data เพื่อหาค่า client_status ที่ถูกต้อง
            $correctStatuses = [];
            foreach ($clients as $client) {
                if (isset($client->raw_data['client_status'])) {
                    $correctStatuses[] = $client->raw_data['client_status'];
                }
            }
            
            if (!empty($correctStatuses)) {
                $this->info("   🔍 พบ client_status ใน raw_data:");
                $uniqueStatuses = array_unique($correctStatuses);
                foreach ($uniqueStatuses as $status) {
                    $count = count(array_filter($correctStatuses, function($s) use ($status) { 
                        return $s === $status; 
                    }));
                    $this->line("      - {$status}: {$count} รายการ");
                }
                
                if (!$showOnly) {
                    // อัพเดต client_status ให้ตรงกับ raw_data
                    $updatedCount = 0;
                    foreach ($clients as $client) {
                        if (isset($client->raw_data['client_status']) && 
                            $client->raw_data['client_status'] !== $client->client_status) {
                            
                            $oldStatus = $client->client_status;
                            $newStatus = $client->raw_data['client_status'];
                            
                            $client->update(['client_status' => $newStatus]);
                            $updatedCount++;
                            $totalUpdated++;
                            
                            $this->line("      🔄 อัพเดต client_uid: {$client->client_uid} จาก {$oldStatus} เป็น {$newStatus}");
                        }
                    }
                    
                    if ($updatedCount > 0) {
                        $this->info("   ✅ อัพเดต client_status เรียบร้อย {$updatedCount} รายการ");
                    } else {
                        $this->info("   ℹ️  client_status ถูกต้องแล้ว ไม่ต้องอัพเดต");
                    }
                } else {
                    $this->info("   📋 โหมดแสดงผลเท่านั้น ไม่ได้อัพเดตข้อมูล");
                }
            } else {
                $this->warn("   ⚠️  ไม่พบ client_status ใน raw_data");
            }
        }

        $this->info("\n🎯 สรุปการตรวจสอบและแก้ไขเสร็จสิ้น!");
        $this->info("=====================================");

        // แสดงสถิติรวม
        $this->info("\n📊 สถิติรวมทั้งหมด:");
        $totalStatusCounts = Client::selectRaw('client_status, COUNT(*) as count')
            ->groupBy('client_status')
            ->get()
            ->pluck('count', 'client_status')
            ->toArray();

        foreach ($totalStatusCounts as $status => $count) {
            $this->line("   - {$status}: {$count} รายการ");
        }

        if (!$showOnly && $totalUpdated > 0) {
            $this->info("\n✅ อัพเดต client_status เรียบร้อย {$totalUpdated} รายการ");
        } elseif ($showOnly) {
            $this->info("\n📋 ตรวจสอบเสร็จสิ้น (โหมดแสดงผลเท่านั้น)");
        } else {
            $this->info("\n✅ ตรวจสอบเสร็จสิ้น - ไม่มีข้อมูลที่ต้องอัพเดต");
        }

        return 0;
    }
} 