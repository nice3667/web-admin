<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\JanischaClient;

echo "=== ตรวจสอบข้อมูล JanischaClient หลังลบข้อมูลจำลอง ===\n";
$total = JanischaClient::count();
echo "จำนวนข้อมูลทั้งหมด: " . $total . "\n";

// ตรวจสอบข้อมูลที่มี client_account ใน raw_data
$withRawAccount = JanischaClient::whereRaw("JSON_EXTRACT(raw_data, '$.client_account') IS NOT NULL")->get();
echo "ข้อมูลที่มี client_account ใน raw_data: " . $withRawAccount->count() . "\n";

// ตรวจสอบข้อมูลที่มี client_id เป็นเลข
$withNumericId = JanischaClient::whereRaw("client_id REGEXP '^[0-9]+$'")->get();
echo "ข้อมูลที่มี client_id เป็นเลข: " . $withNumericId->count() . "\n";

echo "\n=== ตัวอย่างข้อมูล 5 รายการแรก ===\n";
$sampleData = JanischaClient::take(5)->get();
foreach ($sampleData as $client) {
    echo "Client UID: " . $client->client_uid . "\n";
    echo "Client ID: " . $client->client_id . "\n";
    echo "Raw Data: " . json_encode($client->raw_data) . "\n";
    
    // ตรวจสอบ client_account ที่ควรแสดง
    $clientAccount = null;
    if ($client->raw_data && isset($client->raw_data['client_account'])) {
        $clientAccount = $client->raw_data['client_account'];
    } elseif (is_numeric($client->client_id) && !preg_match('/^[A-Z]+/', $client->client_id)) {
        $clientAccount = $client->client_id;
    } elseif (is_numeric($client->client_uid) && !preg_match('/^[A-Z]+/', $client->client_uid)) {
        $clientAccount = $client->client_uid;
    } else {
        $clientAccount = '-';
    }
    
    echo "Client Account ที่ควรแสดง: " . $clientAccount . "\n";
    echo "---\n";
}

echo "\n=== ตรวจสอบข้อมูลที่ไม่มี client_account ===\n";
$noAccount = JanischaClient::whereRaw("JSON_EXTRACT(raw_data, '$.client_account') IS NULL")
    ->whereRaw("client_id NOT REGEXP '^[0-9]+$'")
    ->get();
echo "ข้อมูลที่ไม่มี client_account และ client_id ไม่ใช่เลข: " . $noAccount->count() . "\n";

if ($noAccount->count() > 0) {
    echo "ตัวอย่างข้อมูลที่ไม่มี client_account:\n";
    foreach ($noAccount->take(3) as $client) {
        echo "- Client UID: " . $client->client_uid . ", Client ID: " . $client->client_id . "\n";
    }
} 