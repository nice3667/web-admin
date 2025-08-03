<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Controllers\Admin\CustomersController;
use Illuminate\Http\Request;

echo "=== ทดสอบโครงสร้างข้อมูล API ===\n";

// สร้าง controller instance
$controller = new CustomersController();

// สร้าง request
$request = new Request();

// ทดสอบ allCustomers method
echo "\n--- ทดสอบ allCustomers Method ---\n";
$response = $controller->allCustomers($request);

// ตรวจสอบ response
if (method_exists($response, 'getData')) {
    $data = $response->getData();
    echo "Response type: " . get_class($response) . "\n";
    
    if (isset($data->data->customers)) {
        $customers = $data->data->customers;
        if (is_object($customers) && method_exists($customers, 'toArray')) {
            $customers = $customers->toArray();
        }
        
        echo "จำนวนลูกค้าทั้งหมด: " . count($customers) . "\n";
        
        // ตรวจสอบลูกค้า 159539136
        $targetClient = null;
        foreach ($customers as $client) {
            $clientUid = is_object($client) ? $client->client_uid : $client['client_uid'];
            $clientAccount = is_object($client) ? $client->client_account : $client['client_account'];
            $clientId = is_object($client) ? $client->client_id : $client['client_id'];
            
            if ($clientUid === 'd88df5b9' || $clientAccount === '159539136' || $clientId === '159539136') {
                $targetClient = $client;
                break;
            }
        }
        
        if ($targetClient) {
            echo "\nพบลูกค้า 159539136:\n";
            
            // แสดงข้อมูลทั้งหมดของลูกค้านี้
            if (is_object($targetClient)) {
                foreach (get_object_vars($targetClient) as $key => $value) {
                    echo "  {$key}: " . (is_array($value) ? json_encode($value) : $value) . "\n";
                }
            } else {
                foreach ($targetClient as $key => $value) {
                    echo "  {$key}: " . (is_array($value) ? json_encode($value) : $value) . "\n";
                }
            }
            
            // ตรวจสอบฟิลด์ที่ใช้ในการค้นหา
            echo "\n--- ฟิลด์ที่ใช้ในการค้นหา ---\n";
            $searchFields = [
                'client_account' => is_object($targetClient) ? $targetClient->client_account : $targetClient['client_account'],
                'raw_data?.client_account' => is_object($targetClient) ? $targetClient->raw_data?->client_account : $targetClient['raw_data']['client_account'] ?? null,
                'account_number' => is_object($targetClient) ? $targetClient->account_number : $targetClient['account_number'],
                'login' => is_object($targetClient) ? $targetClient->login : $targetClient['login'],
                'traderId' => is_object($targetClient) ? $targetClient->traderId : $targetClient['traderId'],
                'client_uid' => is_object($targetClient) ? $targetClient->client_uid : $targetClient['client_uid'],
                'client_id' => is_object($targetClient) ? $targetClient->client_id : $targetClient['client_id'],
                'partner_account' => is_object($targetClient) ? $targetClient->partner_account : $targetClient['partner_account'],
                'raw_data?.partner_account' => is_object($targetClient) ? $targetClient->raw_data?->partner_account : $targetClient['raw_data']['partner_account'] ?? null,
                'client_name' => is_object($targetClient) ? $targetClient->client_name : $targetClient['client_name'],
                'raw_data?.client_name' => is_object($targetClient) ? $targetClient->raw_data?->client_name : $targetClient['raw_data']['client_name'] ?? null,
                'country' => is_object($targetClient) ? $targetClient->country : $targetClient['country'],
                'raw_data?.country' => is_object($targetClient) ? $targetClient->raw_data?->country : $targetClient['raw_data']['country'] ?? null
            ];
            
            foreach ($searchFields as $field => $value) {
                echo "  {$field}: " . ($value ?? 'null') . "\n";
            }
            
            // ทดสอบการค้นหา
            echo "\n--- ทดสอบการค้นหา ---\n";
            $searchTerm = '159539136';
            $searchLower = strtolower($searchTerm);
            
            $found = false;
            foreach ($searchFields as $field => $value) {
                if ($value && strtolower(strval($value)) === $searchLower) {
                    echo "  พบในฟิลด์: {$field} = {$value}\n";
                    $found = true;
                }
            }
            
            if (!$found) {
                echo "  ไม่พบการตรงกันแบบ exact match\n";
                
                // ตรวจสอบ partial match
                foreach ($searchFields as $field => $value) {
                    if ($value && strpos(strtolower(strval($value)), $searchLower) !== false) {
                        echo "  พบ partial match ในฟิลด์: {$field} = {$value}\n";
                    }
                }
            }
        } else {
            echo "\nไม่พบลูกค้า 159539136\n";
        }
    }
} else {
    echo "Response type: " . get_class($response) . "\n";
    echo "Response content: " . $response->getContent() . "\n";
}

echo "\n=== การทดสอบเสร็จสิ้น ===\n"; 