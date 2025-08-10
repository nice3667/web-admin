<?php

/**
 * Debug ข้อมูลลูกค้าทั้งหมดของ XM
 * ไฟล์นี้ใช้สำหรับตรวจสอบและวิเคราะห์ข้อมูลลูกค้าจาก XM API
 * 
 * หมายเหตุ: ไฟล์นี้ควรถูกเรียกผ่าน Laravel route ไม่ใช่โดยตรง
 */

// ตรวจสอบว่า Laravel ถูก bootstrap แล้วหรือไม่
if (!function_exists('app')) {
    echo "ข้อผิดพลาด: Laravel ยังไม่ถูก bootstrap\n";
    echo "กรุณาเรียกผ่าน route /debug_xm_clients แทน\n";
    exit;
}

use Illuminate\Support\Facades\DB;

echo "=== DEBUG ข้อมูลลูกค้าทั้งหมดของ XM ===\n";
echo "เวลาเริ่มต้น: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // 1. Database
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

    // 2. XM Page
    echo "\n2. ข้อมูลเฉพาะหน้า XM page:\n";
    echo "----------------------------------------\n";

    // ใช้ partner_account เพื่อแยกแยะข้อมูล XM
    $xmPageClients = DB::table('clients')
        ->where('partner_account', 'like', '%XM%')
        ->orderBy('reg_date', 'desc')
        ->get();

    echo "จำนวนลูกค้าในหน้า XM: " . $xmPageClients->count() . "\n";

    if ($xmPageClients->count() > 0) {
        echo "ลูกค้าล่าสุดในหน้า XM: " . $xmPageClients->first()->client_id . "\n";
        echo "วันที่ลงทะเบียนล่าสุด: " . $xmPageClients->first()->reg_date . "\n";
    }

    // 3. Unique UIDs
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

    // 4. Countries
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

    // 5. Monthly
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

    // 6. Volume & Reward
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

    // 9. Service (ถ้ามี)
    echo "\n9. ข้อมูลจาก XM API:\n";
    echo "----------------------------------------\n";

    if (class_exists('App\\Services\\XmAuthService')) {
        try {
            $xmService = new App\Services\XmAuthService();
            echo "XmAuthService พบและพร้อมใช้งาน\n";
            if (method_exists($xmService, 'getClients')) echo "มี method getClients() ใน XmAuthService\n";
            if (method_exists($xmService, 'getClientCount')) echo "มี method getClientCount() ใน XmAuthService\n";
        } catch (\Throwable $e) {
            echo "เกิดข้อผิดพลาดในการใช้งาน XmAuthService: " . $e->getMessage() . "\n";
        }
    } else {
        echo "ไม่พบ XmAuthService class\n";
    }

    // 10. XM folder
    echo "\n10. ข้อมูลใน XM folder:\n";
    echo "----------------------------------------\n";

    $xmFolderPath = base_path('XM');
    if (is_dir($xmFolderPath)) {
        echo "XM folder พบที่: " . $xmFolderPath . "\n";
        $xmFiles = glob($xmFolderPath . '/*.php') ?: [];
        echo "จำนวนไฟล์ PHP ใน XM folder: " . count($xmFiles) . "\n";
        foreach ($xmFiles as $file) {
            echo "- " . basename($file) . "\n";
        }
    } else {
        echo "ไม่พบ XM folder\n";
    }

    // 11. Backups
    echo "\n11. ข้อมูลใน storage/backups:\n";
    echo "----------------------------------------\n";

    $backupFiles = glob(storage_path('backups/*.json')) ?: [];
    $xmBackups = array_filter($backupFiles, function ($file) {
        return strpos(basename($file), 'clients') !== false;
    });

    echo "จำนวนไฟล์ backup ทั้งหมด: " . count($backupFiles) . "\n";
    echo "จำนวนไฟล์ backup ที่เกี่ยวกับ clients: " . count($xmBackups) . "\n";

    if (count($xmBackups) > 0) {
        $last = end($xmBackups);
        echo "ไฟล์ backup ล่าสุด: " . basename($last) . "\n";
        echo "ขนาดไฟล์: " . number_format(filesize($last) / 1024, 2) . " KB\n";
        echo "แก้ไขล่าสุด: " . date('Y-m-d H:i:s', filemtime($last)) . "\n";
    }

    echo "\n=== สรุป ===\n";
    echo "จำนวนลูกค้าทั้งหมด: " . $dbClients->count() . "\n";
    echo "จำนวน Unique UID: " . $uniqueUids->count() . "\n";
    echo "จำนวนประเทศ: " . $countries->count() . "\n";
    echo "จำนวนเดือนที่มีข้อมูล: " . $monthlyStats->count() . "\n";
    echo "จำนวนไฟล์ backup: " . count($xmBackups) . "\n";
} catch (\Throwable $e) {
    echo "เกิดข้อผิดพลาด: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 