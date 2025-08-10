<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== ตรวจสอบข้อมูลตาราง clients ===\n";

try {
    $totalClients = DB::table('clients')->count();
    echo "จำนวนลูกค้าทั้งหมด: {$totalClients}\n\n";
    
    if ($totalClients > 0) {
        $firstClient = DB::table('clients')->first();
        echo "ข้อมูลลูกค้าคนแรก:\n";
        echo "ID: {$firstClient->id}\n";
        echo "Partner Account: {$firstClient->partner_account}\n";
        echo "Client UID: {$firstClient->client_uid}\n";
        echo "Reg Date: {$firstClient->reg_date}\n";
        echo "Country: {$firstClient->client_country}\n";
        echo "Status: {$firstClient->client_status}\n";
        
        if ($firstClient->raw_data) {
            $rawData = json_decode($firstClient->raw_data, true);
            echo "\nRaw Data:\n";
            if (isset($rawData['partner_account_name'])) {
                echo "Partner Account Name: {$rawData['partner_account_name']}\n";
            }
            if (isset($rawData['partner'])) {
                echo "Partner: {$rawData['partner']}\n";
            }
            
            // ตรวจสอบข้อมูลเพิ่มเติม
            echo "\nข้อมูลเพิ่มเติมใน Raw Data:\n";
            foreach ($rawData as $key => $value) {
                if (is_string($value) && strlen($value) < 100) {
                    echo "{$key}: {$value}\n";
                }
            }
        }
        
        // ตรวจสอบข้อมูลลูกค้าอื่นๆ เพื่อหาประเภทที่แตกต่าง
        echo "\nตรวจสอบข้อมูลลูกค้าอื่นๆ:\n";
        $otherClients = DB::table('clients')->take(5)->get();
        foreach ($otherClients as $client) {
            if ($client->raw_data) {
                $raw = json_decode($client->raw_data, true);
                $partnerName = $raw['partner_account_name'] ?? 'unknown';
                $partner = $raw['partner'] ?? 'unknown';
                echo "Client {$client->id}: partner_account_name='{$partnerName}', partner='{$partner}'\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "เกิดข้อผิดพลาด: " . $e->getMessage() . "\n";
} 