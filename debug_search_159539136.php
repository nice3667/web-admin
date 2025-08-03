<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Controllers\Admin\CustomersController;
use Illuminate\Http\Request;

echo "=== ตรวจสอบข้อมูล 159539136 ===\n";

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
            
            if ($clientUid === '159539136' || $clientAccount === '159539136' || $clientId === '159539136') {
                $targetClient = $client;
                break;
            }
        }
        
        if ($targetClient) {
            echo "\nพบลูกค้า 159539136:\n";
            $clientUid = is_object($targetClient) ? $targetClient->client_uid : $targetClient['client_uid'];
            $clientAccount = is_object($targetClient) ? $targetClient->client_account : $targetClient['client_account'];
            $clientId = is_object($targetClient) ? $targetClient->client_id : $targetClient['client_id'];
            $partnerAccount = is_object($targetClient) ? $targetClient->partner_account : $targetClient['partner_account'];
            $source = is_object($targetClient) ? $targetClient->source : $targetClient['source'];
            $dataSource = is_object($targetClient) ? $targetClient->data_source : $targetClient['data_source'];
            
            echo "Client UID: " . $clientUid . "\n";
            echo "Client Account: " . ($clientAccount ?? 'N/A') . "\n";
            echo "Client ID: " . ($clientId ?? 'N/A') . "\n";
            echo "Partner Account: " . ($partnerAccount ?? 'N/A') . "\n";
            echo "Source: " . ($source ?? 'N/A') . "\n";
            echo "Data Source: " . ($dataSource ?? 'N/A') . "\n";
        } else {
            echo "\nไม่พบลูกค้า 159539136\n";
            
            // ตรวจสอบข้อมูลที่คล้ายกัน
            echo "\n--- ตรวจสอบข้อมูลที่คล้ายกัน ---\n";
            $similarClients = [];
            foreach ($customers as $client) {
                $clientUid = is_object($client) ? $client->client_uid : $client['client_uid'];
                $clientAccount = is_object($client) ? $client->client_account : $client['client_account'];
                $clientId = is_object($client) ? $client->client_id : $client['client_id'];
                
                if (strpos($clientUid, '159539') !== false || 
                    strpos($clientAccount, '159539') !== false || 
                    strpos($clientId, '159539') !== false) {
                    $similarClients[] = $client;
                }
            }
            
            if (count($similarClients) > 0) {
                echo "พบข้อมูลที่คล้ายกัน:\n";
                foreach (array_slice($similarClients, 0, 5) as $client) {
                    $clientUid = is_object($client) ? $client->client_uid : $client['client_uid'];
                    $clientAccount = is_object($client) ? $client->client_account : $client['client_account'];
                    $clientId = is_object($client) ? $client->client_id : $client['client_id'];
                    
                    echo "  Client UID: " . $clientUid . ", Client Account: " . ($clientAccount ?? 'N/A') . ", Client ID: " . ($clientId ?? 'N/A') . "\n";
                }
            } else {
                echo "ไม่พบข้อมูลที่คล้ายกัน\n";
            }
            
            // แสดงตัวอย่างข้อมูล
            if (count($customers) > 0) {
                echo "\n--- ตัวอย่างข้อมูล ---\n";
                $sample = $customers[0];
                $sampleUid = is_object($sample) ? $sample->client_uid : $sample['client_uid'];
                $sampleAccount = is_object($sample) ? $sample->client_account : $sample['client_account'];
                $sampleId = is_object($sample) ? $sample->client_id : $sample['client_id'];
                
                echo "Client UID: " . $sampleUid . "\n";
                echo "Client Account: " . ($sampleAccount ?? 'N/A') . "\n";
                echo "Client ID: " . ($sampleId ?? 'N/A') . "\n";
            }
        }
    }
} else {
    echo "Response type: " . get_class($response) . "\n";
    echo "Response content: " . $response->getContent() . "\n";
}

echo "\n=== การทดสอบเสร็จสิ้น ===\n"; 