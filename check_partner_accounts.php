<?php
// Bootstrap Laravel application
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== ตรวจสอบ Partner Accounts ในฐานข้อมูล ===\n\n";

try {
    // Get distinct partner_accounts
    $partnerAccounts = DB::table('clients')
        ->select('partner_account')
        ->distinct()
        ->get();

    echo "จำนวน Partner Accounts ที่ไม่ซ้ำกัน: " . $partnerAccounts->count() . "\n\n";

    echo "รายการ Partner Accounts:\n";
    echo "----------------------------------------\n";
    
    foreach ($partnerAccounts as $pa) {
        $value = $pa->partner_account ?? 'NULL';
        echo "- " . $value . "\n";
    }

    // Check for NULL values
    $nullCount = DB::table('clients')
        ->whereNull('partner_account')
        ->count();
    echo "- NULL: " . $nullCount . " คน\n";

    // Check for empty string values
    $emptyCount = DB::table('clients')
        ->where('partner_account', '')
        ->count();
    echo "- Empty string: " . $emptyCount . " คน\n";

    // Check for XM-related accounts
    echo "\n=== ตรวจสอบ Partner Accounts ที่เกี่ยวข้องกับ XM ===\n";
    
    $xmAccounts = DB::table('clients')
        ->where('partner_account', 'like', '%XM%')
        ->orWhere('partner_account', 'like', '%xm%')
        ->orWhere('partner_account', 'like', '%XM%')
        ->get();

    echo "จำนวนลูกค้าที่มี Partner Account เกี่ยวข้องกับ XM: " . $xmAccounts->count() . "\n";

    if ($xmAccounts->count() > 0) {
        echo "\nรายการลูกค้า XM:\n";
        foreach ($xmAccounts as $client) {
            echo "- Client ID: " . $client->client_id . ", Partner Account: " . $client->partner_account . "\n";
        }
    }

    // Check for other patterns
    echo "\n=== ตรวจสอบ Partner Accounts อื่นๆ ===\n";
    
    $otherPatterns = [
        'Ham' => '%Ham%',
        'Janischa' => '%Janischa%',
        'Exness' => '%Exness%',
        'Partner' => '%partner%',
        'Campaign' => '%campaign%'
    ];

    foreach ($otherPatterns as $name => $pattern) {
        $count = DB::table('clients')
            ->where('partner_account', 'like', $pattern)
            ->count();
        echo $name . ": " . $count . " คน\n";
    }

    // Show sample data
    echo "\n=== ข้อมูลตัวอย่างจากฐานข้อมูล ===\n";
    $sampleClients = DB::table('clients')
        ->select('client_id', 'client_uid', 'partner_account', 'reg_date')
        ->limit(5)
        ->get();

    foreach ($sampleClients as $client) {
        echo "Client ID: " . $client->client_id . 
             ", UID: " . $client->client_uid . 
             ", Partner Account: " . ($client->partner_account ?? 'NULL') . 
             ", Reg Date: " . $client->reg_date . "\n";
    }

} catch (\Throwable $e) {
    echo "เกิดข้อผิดพลาด: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
