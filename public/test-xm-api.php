<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Http\Request;
use App\Http\Controllers\Admin\XMReportController;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "<h1>ทดสอบการดึงข้อมูล XM API</h1>";

try {
    // สร้าง XM Controller
    $xmController = new XMReportController();
    
    // สร้าง Request object
    $request = new Request();
    
    echo "<h2>กำลังดึงข้อมูลจาก XM API...</h2>";
    
    // เรียกใช้ getTraderList method
    $response = $xmController->getTraderList($request);
    
    echo "<h3>Response Status: " . $response->getStatusCode() . "</h3>";
    
    if ($response->getStatusCode() === 200) {
        $data = json_decode($response->getContent(), true);
        
        if (is_array($data) && !isset($data['error'])) {
            echo "<h3>จำนวนข้อมูลที่ได้: " . count($data) . " รายการ</h3>";
            
            if (count($data) > 0) {
                echo "<h4>ตัวอย่างข้อมูล:</h4>";
                echo "<pre>" . print_r(array_slice($data, 0, 3), true) . "</pre>";
            }
        } else {
            echo "<h3>Error จาก XM API:</h3>";
            echo "<pre>" . print_r($data, true) . "</pre>";
        }
    } else {
        echo "<h3>Error Response:</h3>";
        echo "<pre>" . $response->getContent() . "</pre>";
    }
    
} catch (Exception $e) {
    echo "<h3>Exception:</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h2>ทดสอบการดึงข้อมูลผ่าน CustomersController</h2>";

try {
    // สร้าง Customers Controller
    $customersController = new \App\Http\Controllers\Admin\CustomersController();
    
    // สร้าง Request object
    $request = new Request();
    
    echo "<h3>กำลังดึงข้อมูลผ่าน allCustomers method...</h3>";
    
    // เรียกใช้ allCustomers method
    $response = $customersController->allCustomers($request);
    
    echo "<h4>Response Status: " . $response->getStatusCode() . "</h4>";
    
    if ($response->getStatusCode() === 200) {
        $data = json_decode($response->getContent(), true);
        
        if (is_array($data) && isset($data['data'])) {
            echo "<h4>จำนวนข้อมูลทั้งหมด: " . count($data['data']) . " รายการ</h4>";
            
            // นับข้อมูลจากแต่ละแหล่ง
            $xmCount = 0;
            $hamCount = 0;
            $janischaCount = 0;
            
            foreach ($data['data'] as $customer) {
                if (isset($customer['data_source'])) {
                    if ($customer['data_source'] === 'XM' || $customer['data_source'] === 'XM API') {
                        $xmCount++;
                    } elseif ($customer['data_source'] === 'Ham') {
                        $hamCount++;
                    } elseif ($customer['data_source'] === 'Janischa') {
                        $janischaCount++;
                    }
                }
            }
            
            echo "<h4>สรุปข้อมูลตามแหล่ง:</h4>";
            echo "<ul>";
            echo "<li>XM: " . $xmCount . " รายการ</li>";
            echo "<li>Ham: " . $hamCount . " รายการ</li>";
            echo "<li>Janischa: " . $janischaCount . " รายการ</li>";
            echo "</ul>";
            
            if ($xmCount > 0) {
                echo "<h4>ตัวอย่างข้อมูล XM:</h4>";
                $xmCustomers = array_filter($data['data'], function($customer) {
                    return isset($customer['data_source']) && 
                           ($customer['data_source'] === 'XM' || $customer['data_source'] === 'XM API');
                });
                $xmSample = array_slice($xmCustomers, 0, 2);
                echo "<pre>" . print_r($xmSample, true) . "</pre>";
            }
        } else {
            echo "<h4>ข้อมูลไม่ถูกต้อง:</h4>";
            echo "<pre>" . print_r($data, true) . "</pre>";
        }
    } else {
        echo "<h4>Error Response:</h4>";
        echo "<pre>" . $response->getContent() . "</pre>";
    }
    
} catch (Exception $e) {
    echo "<h3>Exception ใน CustomersController:</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
