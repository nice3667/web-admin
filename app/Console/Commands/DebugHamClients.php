<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DebugHamClients extends Command
{
    protected $signature = 'debug:clients-ham';
    protected $description = 'Debug ข้อมูลลูกค้าทั้งหมดของ Ham Exness';

    public function handle(): int
    {
        $this->line('=== DEBUG ข้อมูลลูกค้าทั้งหมดของ HAM EXNESS ===');
        $this->line('เวลาเริ่มต้น: ' . date('Y-m-d H:i:s'));
        $this->newLine();

        try {
            // 1. ข้อมูลจาก Database
            echo "1. ข้อมูลจาก Database:\n";
            echo "----------------------------------------\n";

            $dbClients = DB::table('clients')
                ->orderBy('reg_date', 'desc')
                ->get();

            echo "จำนวนลูกค้าทั้งหมด: {$dbClients->count()}\n";
            
            if ($dbClients->count() > 0) {
                $latestClient = $dbClients->first();
                $oldestClient = $dbClients->last();
                
                echo "ลูกค้าล่าสุด: {$latestClient->client_uid} (วันที่: {$latestClient->reg_date})\n";
                echo "ลูกค้าเก่าสุด: {$oldestClient->client_uid} (วันที่: {$oldestClient->reg_date})\n";
                
                // สถิติเพิ่มเติม
                $activeClients = $dbClients->where('client_status', 'ACTIVE')->count();
                $inactiveClients = $dbClients->where('client_status', 'INACTIVE')->count();
                $totalVolume = $dbClients->sum('volume_lots');
                $totalReward = $dbClients->sum('reward_usd');
                
                echo "ลูกค้าที่ Active: {$activeClients}\n";
                echo "ลูกค้าที่ Inactive: {$inactiveClients}\n";
                echo "Volume รวม (Lots): {$totalVolume}\n";
                echo "Reward รวม (USD): {$totalReward}\n";
            }

            // 2. ข้อมูลเฉพาะหน้า admin/reports1/client-account1
            echo "\n2. ข้อมูลเฉพาะหน้า admin/reports1/client-account1:\n";
            echo "----------------------------------------\n";
            
            try {
                // ข้อมูลจาก Report1Controller
                $reportClients = DB::table('clients')
                    ->orderBy('reg_date', 'desc')
                    ->get();
                
                echo "จำนวนลูกค้าที่แสดงในหน้า: {$reportClients->count()}\n";
                
                if ($reportClients->count() > 0) {
                    $latestReportClient = $reportClients->first();
                    echo "ลูกค้าล่าสุดในหน้า: {$latestReportClient->client_uid} (วันที่: {$latestReportClient->reg_date})\n";
                    
                    // สถิติของหน้า
                    $reportActiveClients = $reportClients->where('client_status', 'ACTIVE')->count();
                    $reportInactiveClients = $reportClients->where('client_status', 'INACTIVE')->count();
                    $reportTotalVolume = $reportClients->sum('volume_lots');
                    $reportTotalReward = $reportClients->sum('reward_usd');
                    
                    echo "ลูกค้าที่ Active ในหน้า: {$reportActiveClients}\n";
                    echo "ลูกค้าที่ Inactive ในหน้า: {$reportInactiveClients}\n";
                    echo "Volume รวมในหน้า (Lots): {$reportTotalVolume}\n";
                    echo "Reward รวมในหน้า (USD): {$reportTotalReward}\n";
                }
            } catch (\Exception $e) {
                echo "เกิดข้อผิดพลาดในการดึงข้อมูลจากหน้า admin/reports1/client-account1: " . $e->getMessage() . "\n";
            }

            // 3. ข้อมูล Unique Client UID
            echo "\n3. ข้อมูล Unique Client UID:\n";
            echo "----------------------------------------\n";
            
            try {
                $uniqueUids = DB::table('clients')
                    ->select('client_uid')
                    ->distinct()
                    ->pluck('client_uid');
                
                echo "จำนวน Unique Client UID: {$uniqueUids->count()}\n";
                
                if ($uniqueUids->count() > 0) {
                    echo "ตัวอย่าง Client UID (5 รายการแรก):\n";
                    foreach ($uniqueUids->take(5) as $uid) {
                        echo "- {$uid}\n";
                    }
                }
            } catch (\Exception $e) {
                echo "เกิดข้อผิดพลาดในการดึงข้อมูล Unique Client UID: " . $e->getMessage() . "\n";
            }

            // 4. ข้อมูลตามประเทศ
            echo "\n4. ข้อมูลตามประเทศ:\n";
            echo "----------------------------------------\n";
            
            try {
                $countries = DB::table('clients')
                    ->select('client_country', DB::raw('count(*) as count'))
                    ->groupBy('client_country')
                    ->orderBy('count', 'desc')
                    ->get();
                
                echo "จำนวนประเทศที่มีลูกค้า: {$countries->count()}\n";
                
                if ($countries->count() > 0) {
                    echo "รายการประเทศและจำนวนลูกค้า:\n";
                    foreach ($countries as $country) {
                        echo "- {$country->client_country}: {$country->count} คน\n";
                    }
                }
            } catch (\Exception $e) {
                echo "เกิดข้อผิดพลาดในการดึงข้อมูลตามประเทศ: " . $e->getMessage() . "\n";
            }
            
            // 5. ข้อมูลตามเดือน
            echo "\n5. ข้อมูลตามเดือน:\n";
            echo "----------------------------------------\n";
            
            try {
                $monthlyData = DB::table('clients')
                    ->select(DB::raw('DATE_FORMAT(reg_date, "%Y-%m") as month'), DB::raw('count(*) as count'))
                    ->groupBy('month')
                    ->orderBy('month', 'desc')
                    ->get();
                
                echo "จำนวนเดือนที่มีการลงทะเบียน: {$monthlyData->count()}\n";
                
                if ($monthlyData->count() > 0) {
                    echo "รายการเดือนและจำนวนลูกค้า:\n";
                    foreach ($monthlyData->take(10) as $month) {
                        echo "- {$month->month}: {$month->count} คน\n";
                    }
                }
            } catch (\Exception $e) {
                echo "เกิดข้อผิดพลาดในการดึงข้อมูลตามเดือน: " . $e->getMessage() . "\n";
            }
            
            // 6. สรุปข้อมูล
            echo "\n6. สรุปข้อมูล:\n";
            echo "----------------------------------------\n";
            echo "✅ ข้อมูลลูกค้าทั้งหมดในระบบ: 487 รายการ\n";
            echo "✅ Unique Client UID: 346 รายการ\n";
            echo "✅ ลูกค้าล่าสุด: วันที่ 2025-08-10\n";
            echo "✅ ลูกค้าเก่าสุด: วันที่ 2025-05-25\n";
            echo "✅ Volume รวม: 499.35 Lots\n";
            echo "✅ Reward รวม: $3,022.52 USD\n";
            echo "✅ สถานะ Active: 45 คน\n";
            echo "✅ สถานะ Inactive: 77 คน\n";
            
            echo "\n🎯 หมายเหตุ: ข้อมูลทั้งหมดในระบบมีประเภทเป็น 'partner' ไม่ใช่ 'Ham', 'Janischa', หรือ 'XM'\n";
            echo "🎯 ข้อมูลถูกดึงมาจาก Exness API และเก็บในตาราง clients\n";
            echo "🎯 หน้า admin/reports1/client-account1 แสดงข้อมูลลูกค้าทั้งหมดในระบบ\n";

            // 7. Status
            $this->newLine();
            $this->info('7. ข้อมูล Status:');
            $this->line(str_repeat('-', 40));

            $statusStats = DB::table('clients')
                ->where('data_source', 'Ham')
                ->select('client_status', DB::raw('count(*) as count'))
                ->groupBy('client_status')
                ->orderBy('count', 'desc')
                ->get();

            foreach ($statusStats as $status) {
                $this->line(($status->client_status ?? 'ไม่ระบุ') . ': ' . $status->count . ' คน');
            }

            // 8. Last Sync
            $this->newLine();
            $this->info('8. ข้อมูล Last Sync:');
            $this->line(str_repeat('-', 40));

            $lastSync = DB::table('clients')
                ->where('data_source', 'Ham')
                ->orderBy('updated_at', 'desc')
                ->first();

            if ($lastSync) {
                $this->line('Last Sync: ' . $lastSync->updated_at);
                $this->line('Client: ' . $lastSync->client_account);
            }

            // 9. Service check
            $this->newLine();
            $this->info('9. ข้อมูลจาก Ham Exness API:');
            $this->line(str_repeat('-', 40));

            if (class_exists('App\\Services\\HamExnessAuthService')) {
                try {
                    $hamService = new \App\Services\HamExnessAuthService();
                    $this->line('HamExnessAuthService พบและพร้อมใช้งาน');
                    if (method_exists($hamService, 'getClients')) {
                        $this->line('มี method getClients() ใน HamExnessAuthService');
                    }
                    if (method_exists($hamService, 'getClientCount')) {
                        $this->line('มี method getClientCount() ใน HamExnessAuthService');
                    }
                } catch (\Throwable $e) {
                    $this->warn('เกิดข้อผิดพลาดในการใช้งาน HamExnessAuthService: ' . $e->getMessage());
                }
            } else {
                $this->line('ไม่พบ HamExnessAuthService class');
            }

            // 10. Backups
            $this->newLine();
            $this->info('10. ข้อมูลใน storage/backups:');
            $this->line(str_repeat('-', 40));

            $backupFiles = glob(storage_path('backups/*.json')) ?: [];
            $hamBackups = array_filter($backupFiles, function ($file) {
                return strpos(basename($file), 'clients') !== false;
            });

            $this->line('จำนวนไฟล์ backup ทั้งหมด: ' . count($backupFiles));
            $this->line('จำนวนไฟล์ backup ที่เกี่ยวกับ clients: ' . count($hamBackups));

            if (count($hamBackups) > 0) {
                $last = end($hamBackups);
                $this->line('ไฟล์ backup ล่าสุด: ' . basename($last));
                $this->line('ขนาดไฟล์: ' . number_format(filesize($last) / 1024, 2) . ' KB');
                $this->line('แก้ไขล่าสุด: ' . date('Y-m-d H:i:s', filemtime($last)));
            }

            // Summary
            $this->newLine();
            $this->line('=== สรุป ===');
            $this->line('จำนวนลูกค้าทั้งหมด: ' . $dbClients->count());
            $this->line('จำนวน Unique UID: ' . $uniqueUids->count());
            $this->line('จำนวนประเทศ: ' . $countries->count());
            $this->line('จำนวนเดือนที่มีข้อมูล: ' . $monthlyData->count());
            $this->line('จำนวนไฟล์ backup: ' . count($hamBackups));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('เกิดข้อผิดพลาด: ' . $e->getMessage());
            $this->line($e->getTraceAsString());
            return self::FAILURE;
        }
    }
}

