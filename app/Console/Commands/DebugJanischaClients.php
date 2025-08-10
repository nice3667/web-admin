<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DebugJanischaClients extends Command
{
    protected $signature = 'debug:clients-janischa';
    protected $description = 'Debug ข้อมูลลูกค้าทั้งหมดของ Janischa Exness';

    public function handle(): int
    {
        $this->line('=== DEBUG ข้อมูลลูกค้าทั้งหมดของ JANISCHA ===');
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

            // 3. Countries
            $this->newLine();
            $this->info('3. ข้อมูลตามประเทศ:');
            $this->line(str_repeat('-', 40));

            $countries = DB::table('clients')
                ->where('data_source', 'Janischa')
                ->select('client_country', DB::raw('count(*) as count'))
                ->groupBy('client_country')
                ->orderBy('count', 'desc')
                ->get();

            foreach ($countries as $country) {
                $this->line(($country->client_country ?? 'ไม่ระบุ') . ': ' . $country->count . ' คน');
            }

            // 4. Monthly
            $this->newLine();
            $this->info('4. ข้อมูลตามเดือน:');
            $this->line(str_repeat('-', 40));

            $monthlyStats = DB::table('clients')
                ->where('data_source', 'Janischa')
                ->select(DB::raw('DATE_FORMAT(reg_date, "%Y-%m") as month'), DB::raw('count(*) as count'))
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->get();

            foreach ($monthlyStats as $month) {
                $this->line($month->month . ': ' . $month->count . ' คน');
            }

            // 5. Volume & Reward
            $this->newLine();
            $this->info('5. ข้อมูล Volume และ Reward:');
            $this->line(str_repeat('-', 40));

            $volumeStats = DB::table('clients')
                ->where('data_source', 'Janischa')
                ->select(
                    DB::raw('SUM(volume_lots) as total_volume'),
                    DB::raw('SUM(reward_usd) as total_reward'),
                    DB::raw('AVG(volume_lots) as avg_volume'),
                    DB::raw('AVG(reward_usd) as avg_reward')
                )
                ->first();

            $this->line('Total Volume: ' . number_format((float)($volumeStats->total_volume ?? 0), 2) . ' lots');
            $this->line('Total Reward: $' . number_format((float)($volumeStats->total_reward ?? 0), 2));
            $this->line('Average Volume: ' . number_format((float)($volumeStats->avg_volume ?? 0), 2) . ' lots');
            $this->line('Average Reward: $' . number_format((float)($volumeStats->avg_reward ?? 0), 2));

            // 6. Status
            $this->newLine();
            $this->info('6. ข้อมูล Status:');
            $this->line(str_repeat('-', 40));

            $statusStats = DB::table('clients')
                ->where('data_source', 'Janischa')
                ->select('client_status', DB::raw('count(*) as count'))
                ->groupBy('client_status')
                ->orderBy('count', 'desc')
                ->get();

            foreach ($statusStats as $status) {
                $this->line(($status->client_status ?? 'ไม่ระบุ') . ': ' . $status->count . ' คน');
            }

            // 7. Backups
            $this->newLine();
            $this->info('7. ข้อมูลใน storage/backups:');
            $this->line(str_repeat('-', 40));

            $backupFiles = glob(storage_path('backups/*.json')) ?: [];
            $janischaBackups = array_filter($backupFiles, function ($file) {
                return strpos(basename($file), 'clients') !== false;
            });

            $this->line('จำนวนไฟล์ backup ทั้งหมด: ' . count($backupFiles));
            $this->line('จำนวนไฟล์ backup ที่เกี่ยวกับ clients: ' . count($janischaBackups));

            if (count($janischaBackups) > 0) {
                $last = end($janischaBackups);
                $this->line('ไฟล์ backup ล่าสุด: ' . basename($last));
                $this->line('ขนาดไฟล์: ' . number_format(filesize($last) / 1024, 2) . ' KB');
                $this->line('แก้ไขล่าสุด: ' . date('Y-m-d H:i:s', filemtime($last)));
            }

            // Summary
            $this->newLine();
            $this->line('=== สรุป ===');
            $this->line('จำนวนลูกค้าทั้งหมด: ' . $dbClients->count());
            $this->line('จำนวนประเทศ: ' . $countries->count());
            $this->line('จำนวนเดือนที่มีข้อมูล: ' . $monthlyStats->count());
            $this->line('จำนวนไฟล์ backup: ' . count($janischaBackups));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('เกิดข้อผิดพลาด: ' . $e->getMessage());
            $this->line($e->getTraceAsString());
            return self::FAILURE;
        }
    }
}

