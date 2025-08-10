<?php

/**
 * Debug ข้อมูล partner_account ในฐานข้อมูล
 * ไฟล์นี้ใช้สำหรับตรวจสอบและวิเคราะห์ข้อมูล partner_account
 */

// ตรวจสอบว่า Laravel ถูก bootstrap แล้วหรือไม่
if (!function_exists('app')) {
    echo "ข้อผิดพลาด: Laravel ยังไม่ถูก bootstrap\n";
    echo "กรุณาเรียกผ่าน route /debug-partner-accounts แทน\n";
    exit;
}

use Illuminate\Support\Facades\DB;

echo "=== DEBUG ข้อมูล partner_account ในฐานข้อมูล ===\n";
echo "เวลาเริ่มต้น: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // 1. ตรวจสอบข้อมูล partner_account ทั้งหมด
    echo "1. ข้อมูล partner_account ทั้งหมด:\n";
    echo "----------------------------------------\n";

    $partnerAccounts = DB::table('clients')
        ->select('partner_account', DB::raw('count(*) as count'))
        ->groupBy('partner_account')
        ->orderBy('count', 'desc')
        ->get();

    echo "จำนวน partner_account ที่แตกต่างกัน: " . $partnerAccounts->count() . "\n\n";

    foreach ($partnerAccounts as $account) {
        echo ($account->partner_account ?? 'NULL') . ": " . $account->count . " คน\n";
    }

    // 2. ตรวจสอบข้อมูลที่ไม่มี partner_account
    echo "\n2. ข้อมูลที่ไม่มี partner_account:\n";
    echo "----------------------------------------\n";

    $nullCount = DB::table('clients')
        ->whereNull('partner_account')
        ->count();

    echo "จำนวนลูกค้าที่ไม่มี partner_account: " . $nullCount . " คน\n";

    $emptyCount = DB::table('clients')
        ->where('partner_account', '')
        ->count();

    echo "จำนวนลูกค้าที่มี partner_account เป็นค่าว่าง: " . $emptyCount . " คน\n";

    // 3. ตรวจสอบข้อมูลที่อาจเป็น XM
    echo "\n3. ข้อมูลที่อาจเป็น XM:\n";
    echo "----------------------------------------\n";

    $xmAccounts = DB::table('clients')
        ->where('partner_account', 'like', '%XM%')
        ->orWhere('partner_account', 'like', '%xm%')
        ->orWhere('partner_account', 'like', '%Xm%')
        ->orWhere('partner_account', 'like', '%xM%')
        ->get();

    echo "จำนวนลูกค้าที่มี partner_account ที่มีคำว่า XM: " . $xmAccounts->count() . " คน\n";

    if ($xmAccounts->count() > 0) {
        foreach ($xmAccounts as $client) {
            echo "- Client ID: " . ($client->client_id ?? 'N/A') . 
                 ", Partner Account: " . ($client->partner_account ?? 'N/A') . 
                 ", UID: " . ($client->client_uid ?? 'N/A') . "\n";
        }
    }

    // 4. ตรวจสอบข้อมูลใน raw_data ที่อาจมีข้อมูล XM
    echo "\n4. ข้อมูลใน raw_data ที่อาจมีข้อมูล XM:\n";
    echo "----------------------------------------\n";

    $count = DB::table('clients')
        ->whereRaw("JSON_EXTRACT(raw_data, '$.partner_account_name') IS NOT NULL")
        ->count();

    echo "จำนวนลูกค้าที่มี partner_account_name ใน raw_data: " . $count . " คน\n";

    if ($count > 0) {
        $sampleClients = DB::table('clients')
            ->whereRaw("JSON_EXTRACT(raw_data, '$.partner_account_name') IS NOT NULL")
            ->take(5)
            ->get();

        echo "\nตัวอย่างข้อมูล:\n";
        foreach ($sampleClients as $client) {
            $rawData = $client->raw_data ?? [];
            $partnerAccountName = is_array($rawData) ? ($rawData['partner_account_name'] ?? 'N/A') : 'N/A';
            echo "- Client ID: " . ($client->client_id ?? 'N/A') . 
                 ", Partner Account Name: " . $partnerAccountName . 
                 ", UID: " . ($client->client_uid ?? 'N/A') . "\n";
        }
    }

    // 5. ตรวจสอบข้อมูลทั้งหมดในตาราง clients
    echo "\n5. โครงสร้างข้อมูลในตาราง clients:\n";
    echo "----------------------------------------\n";

    $totalClients = DB::table('clients')->count();
    echo "จำนวนลูกค้าทั้งหมด: " . $totalClients . " คน\n";

    if ($totalClients > 0) {
        $firstClient = DB::table('clients')->first();
        echo "\nตัวอย่างข้อมูลลูกค้าคนแรก:\n";
        foreach ($firstClient as $key => $value) {
            if (is_string($value) && strlen($value) > 100) {
                echo "- " . $key . ": " . substr($value, 0, 100) . "... (truncated)\n";
            } else {
                echo "- " . $key . ": " . ($value ?? 'NULL') . "\n";
            }
        }
    }

} catch (\Throwable $e) {
    echo "ข้อผิดพลาด: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n=== สรุป ===\n";
echo "ตรวจสอบข้อมูล partner_account ในฐานข้อมูลเสร็จสิ้น\n";
