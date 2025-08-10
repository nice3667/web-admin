<?php

/**
 * Debug ข้อมูลลูกค้าทั้งหมดของ Ham Exness
 * ไฟล์นี้ใช้สำหรับตรวจสอบและวิเคราะห์ข้อมูลลูกค้าจาก Ham Exness API
 * 
 * หมายเหตุ: ไฟล์นี้ควรถูกเรียกผ่าน Laravel route ไม่ใช่โดยตรง
 */

// ตรวจสอบว่า Laravel ถูก bootstrap แล้วหรือไม่
if (!function_exists('app')) {
    echo "ข้อผิดพลาด: Laravel ยังไม่ถูก bootstrap\n";
    echo "กรุณาเรียกผ่าน route /debug_ham_clients แทน\n";
    exit;
}

use Illuminate\Support\Facades\DB;

// พิมพ์ส่วนหัว
echo "=== DEBUG ข้อมูลลูกค้าทั้งหมดของ HAM EXNESS ===\n";
echo "เวลาเริ่มต้น: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // 1. ตรวจสอบข้อมูลจาก Database
    echo "1. ข้อมูลจาก Database:\n";
    echo "----------------------------------------\n";

    $dbClients = DB::table('clients')
        ->orderBy('reg_date', 'desc')
        ->get();

    echo "จำนวนลูกค้าทั้งหมด: " . $dbClients->count() . "\n";

    if ($dbClients->count() > 0) {
        echo "ลูกค้าล่าสุด: " . $dbClients->first()->client_id . " (UID: " . $dbClients->first()->client_uid . ")\n";
        echo "วันที่ลงทะเบียนล่าสุด: " . $dbClients->first()->reg_date . "\n";
        echo "ลูกค้าเก่าสุด: " . $dbClients->last()->client_id . " (UID: " . $dbClients->last()->client_uid . ")\n";
        echo "วันที่ลงทะเบียนเก่าสุด: " . $dbClients->last()->reg_date . "\n";
    }

    // 2. ตรวจสอบข้อมูลเฉพาะหน้า admin/reports1/client-account1
    echo "\n2. ข้อมูลเฉพาะหน้า admin/reports1/client-account1:\n";
    echo "----------------------------------------\n";

    // ใช้ partner_account เพื่อแยกแยะข้อมูล
    $clientAccount1Clients = DB::table('clients')
        ->where('partner_account', 'like', '%1172984151037556173%')
        ->orderBy('reg_date', 'desc')
        ->get();

    echo "จำนวนลูกค้าในหน้า client-account1: " . $clientAccount1Clients->count() . "\n";

    if ($clientAccount1Clients->count() > 0) {
        echo "ลูกค้าล่าสุดในหน้า client-account1: " . $clientAccount1Clients->first()->client_id . "\n";
        echo "วันที่ลงทะเบียนล่าสุด: " . $clientAccount1Clients->first()->reg_date . "\n";
    }

    // 3. ตรวจสอบข้อมูล Unique Client UID
    echo "\n3. ข้อมูล Unique Client UID:\n";
    echo "----------------------------------------\n";

    $uniqueUids = DB::table('clients')
        ->distinct()
        ->pluck('client_uid');

    echo "จำนวน Unique Client UID: " . $uniqueUids->count() . "\n";

    if ($uniqueUids->count() > 0) {
        echo "UID ล่าสุด: " . $uniqueUids->first() . "\n";
        echo "UID เก่าสุด: " . $uniqueUids->last() . "\n";
    }

    // 4. ข้อมูลตามประเทศ
    echo "\n4. ข้อมูลตามประเทศ:\n";
    echo "----------------------------------------\n";

    $countries = DB::table('clients')
        ->select('client_country', DB::raw('count(*) as count'))
        ->groupBy('client_country')
        ->orderBy('count', 'desc')
        ->get();

    foreach ($countries as $country) {
        echo ($country->client_country ?? 'ไม่ระบุ') . ": " . $country->count . " คน\n";
    }

    // 5. ข้อมูลตามเดือน
    echo "\n5. ข้อมูลตามเดือน:\n";
    echo "----------------------------------------\n";

    $monthlyStats = DB::table('clients')
        ->select(DB::raw('DATE_FORMAT(reg_date, "%Y-%m") as month'), DB::raw('count(*) as count'))
        ->groupBy('month')
        ->orderBy('month', 'desc')
        ->get();

    foreach ($monthlyStats as $month) {
        echo $month->month . ": " . $month->count . " คน\n";
    }

    // 6. Volume และ Reward
    echo "\n6. ข้อมูล Volume และ Reward:\n";
    echo "----------------------------------------\n";

    $volumeStats = DB::table('clients')
        ->select(
            DB::raw('SUM(volume_lots) as total_volume'),
            DB::raw('SUM(reward_usd) as total_reward'),
            DB::raw('AVG(volume_lots) as avg_volume'),
            DB::raw('AVG(reward_usd) as avg_reward')
        )
        ->first();

    echo "Total Volume: " . number_format((float)($volumeStats->total_volume ?? 0), 2) . " lots\n";
    echo "Total Reward: $" . number_format((float)($volumeStats->total_reward ?? 0), 2) . "\n";
    echo "Average Volume: " . number_format((float)($volumeStats->avg_volume ?? 0), 2) . " lots\n";
    echo "Average Reward: $" . number_format((float)($volumeStats->avg_reward ?? 0), 2) . "\n";

    // 7. Status
    echo "\n7. ข้อมูล Status:\n";
    echo "----------------------------------------\n";

    $statusStats = DB::table('clients')
        ->select('client_status', DB::raw('count(*) as count'))
        ->groupBy('client_status')
        ->orderBy('count', 'desc')
        ->get();

    foreach ($statusStats as $status) {
        echo ($status->client_status ?? 'ไม่ระบุ') . ": " . $status->count . " คน\n";
    }

    // 8. Last Sync
    echo "\n8. ข้อมูล Last Sync:\n";
    echo "----------------------------------------\n";

    $lastSync = DB::table('clients')
        ->orderBy('updated_at', 'desc')
        ->first();

    if ($lastSync) {
        echo "Last Sync: " . $lastSync->updated_at . "\n";
        echo "Client: " . $lastSync->client_id . "\n";
    }

    // 9. ตรวจสอบ Service (ถ้ามี)
    echo "\n9. ข้อมูลจาก Ham Exness API:\n";
    echo "----------------------------------------\n";

    if (class_exists('App\\Services\\HamExnessAuthService')) {
        try {
            $hamService = new App\Services\HamExnessAuthService();
            echo "HamExnessAuthService พบและพร้อมใช้งาน\n";
            if (method_exists($hamService, 'getClients')) {
                echo "มี method getClients() ใน HamExnessAuthService\n";
            }
            if (method_exists($hamService, 'getClientCount')) {
                echo "มี method getClientCount() ใน HamExnessAuthService\n";
            }
        } catch (\Throwable $e) {
            echo "เกิดข้อผิดพลาดในการใช้งาน HamExnessAuthService: " . $e->getMessage() . "\n";
        }
    } else {
        echo "ไม่พบ HamExnessAuthService class\n";
    }

    // 10. ไฟล์สำรอง backup
    echo "\n10. ข้อมูลใน storage/backups:\n";
    echo "----------------------------------------\n";

    $backupFiles = glob(storage_path('backups/*.json')) ?: [];
    $hamBackups = array_filter($backupFiles, function ($file) {
        return strpos(basename($file), 'clients') !== false;
    });

    echo "จำนวนไฟล์ backup ทั้งหมด: " . count($backupFiles) . "\n";
    echo "จำนวนไฟล์ backup ที่เกี่ยวกับ clients: " . count($hamBackups) . "\n";

    if (count($hamBackups) > 0) {
        $last = end($hamBackups);
        echo "ไฟล์ backup ล่าสุด: " . basename($last) . "\n";
        echo "ขนาดไฟล์: " . number_format(filesize($last) / 1024, 2) . " KB\n";
        echo "แก้ไขล่าสุด: " . date('Y-m-d H:i:s', filemtime($last)) . "\n";
    }

    echo "\n=== สรุป ===\n";
    echo "จำนวนลูกค้าทั้งหมด: " . $dbClients->count() . "\n";
    echo "จำนวน Unique UID: " . $uniqueUids->count() . "\n";
    echo "จำนวนประเทศ: " . $countries->count() . "\n";
    echo "จำนวนเดือนที่มีข้อมูล: " . $monthlyStats->count() . "\n";
    echo "จำนวนไฟล์ backup: " . count($hamBackups) . "\n";
    
} catch (\Throwable $e) {
    echo "เกิดข้อผิดพลาด: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 