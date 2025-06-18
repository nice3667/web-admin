<?php
/**
 * Test Script สำหรับทดสอบ Client Sync API
 * 
 * วิธีการใช้งาน:
 * 1. รัน Laravel server: php artisan serve
 * 2. รัน script นี้: php test_client_sync.php
 */

// ตั้งค่า base URL (เปลี่ยนตามการตั้งค่า server ของคุณ)
$baseUrl = 'http://localhost:8000';

// ฟังก์ชันสำหรับเรียก API
function callApi($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

echo "=== ทดสอบ Client Sync API ===\n\n";

// 1. ทดสอบดูสถิติการ sync
echo "1. ดูสถิติการ sync:\n";
$stats = callApi($baseUrl . '/api/clients/sync-stats');
if ($stats['status'] === 200) {
    echo "✅ สำเร็จ\n";
    echo "   - จำนวนลูกค้าทั้งหมด: " . ($stats['data']['data']['total_clients'] ?? 'N/A') . "\n";
    echo "   - Sync ล่าสุด: " . ($stats['data']['data']['last_sync'] ?? 'N/A') . "\n";
    echo "   - Sync วันนี้: " . ($stats['data']['data']['clients_synced_today'] ?? 'N/A') . "\n";
} else {
    echo "❌ ล้มเหลว (HTTP {$stats['status']})\n";
}
echo "\n";

// 2. ทดสอบดูข้อมูลลูกค้า
echo "2. ดูข้อมูลลูกค้า:\n";
$clients = callApi($baseUrl . '/api/clients');
if ($clients['status'] === 200) {
    echo "✅ สำเร็จ\n";
    echo "   - จำนวนลูกค้า: " . count($clients['data']['data']['clients'] ?? []) . "\n";
    echo "   - สถิติ: " . json_encode($clients['data']['data']['stats'] ?? []) . "\n";
} else {
    echo "❌ ล้มเหลว (HTTP {$clients['status']})\n";
}
echo "\n";

// 3. ทดสอบ sync เฉพาะลูกค้าใหม่
echo "3. Sync เฉพาะลูกค้าใหม่:\n";
$syncNew = callApi($baseUrl . '/api/clients/sync-new', 'POST');
if ($syncNew['status'] === 200) {
    echo "✅ สำเร็จ\n";
    echo "   - ลูกค้าใหม่ที่เพิ่ม: " . ($syncNew['data']['data']['new_clients_added'] ?? 'N/A') . "\n";
    echo "   - จำนวนลูกค้าใน API: " . ($syncNew['data']['data']['total_api_clients'] ?? 'N/A') . "\n";
    echo "   - จำนวนลูกค้าในฐานข้อมูล: " . ($syncNew['data']['data']['existing_clients'] ?? 'N/A') . "\n";
} else {
    echo "❌ ล้มเหลว (HTTP {$syncNew['status']})\n";
    if (isset($syncNew['data']['message'])) {
        echo "   - ข้อผิดพลาด: " . $syncNew['data']['message'] . "\n";
    }
}
echo "\n";

// 4. ทดสอบ sync ข้อมูลทั้งหมด
echo "4. Sync ข้อมูลทั้งหมด:\n";
$syncAll = callApi($baseUrl . '/api/clients/sync', 'POST');
if ($syncAll['status'] === 200) {
    echo "✅ สำเร็จ\n";
    echo "   - ข้อความ: " . ($syncAll['data']['message'] ?? 'N/A') . "\n";
} else {
    echo "❌ ล้มเหลว (HTTP {$syncAll['status']})\n";
    if (isset($syncAll['data']['message'])) {
        echo "   - ข้อผิดพลาด: " . $syncAll['data']['message'] . "\n";
    }
}
echo "\n";

// 5. ทดสอบ debug API
echo "5. Debug API:\n";
$debugApi = callApi($baseUrl . '/api/clients/debug');
if ($debugApi['status'] === 200) {
    echo "✅ สำเร็จ\n";
    echo "   - V1 API Clients: " . ($debugApi['data']['v1_api']['total_clients'] ?? 'N/A') . "\n";
    echo "   - V2 API Clients: " . ($debugApi['data']['v2_api']['total_clients'] ?? 'N/A') . "\n";
    echo "   - Matching UIDs: " . ($debugApi['data']['matching_analysis']['matching_uids_count'] ?? 'N/A') . "\n";
} else {
    echo "❌ ล้มเหลว (HTTP {$debugApi['status']})\n";
}
echo "\n";

// 6. ทดสอบ debug Database
echo "6. Debug Database:\n";
$debugDb = callApi($baseUrl . '/api/clients/debug-db');
if ($debugDb['status'] === 200) {
    echo "✅ สำเร็จ\n";
    echo "   - จำนวนลูกค้าทั้งหมด: " . ($debugDb['data']['database_status']['total_clients'] ?? 'N/A') . "\n";
    echo "   - มี rebate column: " . ($debugDb['data']['database_status']['has_rebate_column'] ? 'ใช่' : 'ไม่') . "\n";
    echo "   - การกระจายสถานะ: " . json_encode($debugDb['data']['database_status']['status_distribution'] ?? []) . "\n";
} else {
    echo "❌ ล้มเหลว (HTTP {$debugDb['status']})\n";
}
echo "\n";

echo "=== การทดสอบเสร็จสิ้น ===\n";
echo "\nหมายเหตุ: หากมีข้อผิดพลาด ให้ตรวจสอบ:\n";
echo "1. Laravel server กำลังทำงานอยู่หรือไม่\n";
echo "2. URL ในตัวแปร \$baseUrl ถูกต้องหรือไม่\n";
echo "3. การเชื่อมต่อฐานข้อมูล\n";
echo "4. ข้อมูล API credentials\n"; 