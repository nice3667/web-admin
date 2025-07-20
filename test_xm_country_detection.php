<?php

require_once 'vendor/autoload.php';

// Test the XM country detection endpoint
$baseUrl = 'http://localhost/admin/xm/check-missing-country';

echo "🧪 ทดสอบการตรวจหาประเทศของ user ที่ไม่มีข้อมูลประเทศ\n";
echo "=" . str_repeat("=", 60) . "\n";

try {
    // Initialize HTTP client
    $client = new \GuzzleHttp\Client();
    
    // Test with last 30 days
    $response = $client->request('GET', $baseUrl, [
        'query' => [
            'startTime' => date('Y-m-d', strtotime('-30 days')),
            'endTime' => date('Y-m-d')
        ],
        'timeout' => 30
    ]);
    
    if ($response->getStatusCode() !== 200) {
        throw new Exception('Failed to fetch data. Status: ' . $response->getStatusCode());
    }
    
    $data = json_decode($response->getBody()->getContents(), true);
    
    if (!$data) {
        throw new Exception('Invalid response data');
    }
    
    // Display summary
    $summary = $data['summary'];
    echo "📊 สรุปผลการตรวจสอบ:\n";
    echo "   - จำนวน user ทั้งหมด: {$summary['total_users']} คน\n";
    echo "   - จำนวน user ที่ไม่มีข้อมูลประเทศ: {$summary['missing_country_count']} คน\n";
    echo "   - เปอร์เซ็นต์: {$summary['percentage']}%\n";
    echo "   - ช่วงเวลา: {$summary['date_range']}\n";
    echo "\n";
    
    // Display detection statistics
    if (isset($data['country_detection_stats'])) {
        $stats = $data['country_detection_stats'];
        echo "📈 สถิติการตรวจหาประเทศ:\n";
        echo "   - ตรวจหาได้ทั้งหมด: {$stats['total_missing']} คน\n";
        
        if (isset($stats['detected_countries']) && !empty($stats['detected_countries'])) {
            echo "   - ประเทศที่ตรวจพบ:\n";
            foreach ($stats['detected_countries'] as $country => $count) {
                echo "     * {$country}: {$count} คน\n";
            }
        }
        
        if (isset($stats['detection_methods']) && !empty($stats['detection_methods'])) {
            echo "   - วิธีการตรวจหา:\n";
            foreach ($stats['detection_methods'] as $method => $count) {
                echo "     * {$method}: {$count} คน\n";
            }
        }
        
        echo "   - ระดับความเชื่อมั่น:\n";
        echo "     * สูง (60%+): {$stats['confidence_levels']['high']} คน\n";
        echo "     * ปานกลาง (30-59%): {$stats['confidence_levels']['medium']} คน\n";
        echo "     * ต่ำ (1-29%): {$stats['confidence_levels']['low']} คน\n";
        echo "     * ไม่สามารถตรวจหาได้: {$stats['confidence_levels']['none']} คน\n";
        echo "\n";
    }
    
    // Display sample missing country users
    if (isset($data['missing_country_users']) && !empty($data['missing_country_users'])) {
        echo "👥 ตัวอย่าง user ที่ไม่มีข้อมูลประเทศ (แสดง 5 คนแรก):\n";
        echo str_repeat("-", 60) . "\n";
        
        $sampleUsers = array_slice($data['missing_country_users'], 0, 5);
        
        foreach ($sampleUsers as $index => $user) {
            $num = $index + 1;
            $detection = $user['detectedCountry'];
            
            echo "{$num}. Trader ID: {$user['traderId']}\n";
            echo "   Campaign: {$user['campaign']}\n";
            echo "   ประเทศที่ตรวจพบ: {$detection['country_name']}\n";
            echo "   ความเชื่อมั่น: {$detection['confidence']}%\n";
            echo "   วิธีการตรวจหา: {$detection['detection_method']}\n";
            echo "\n";
        }
    }
    
    echo "✅ การทดสอบเสร็จสิ้น\n";
    
} catch (Exception $e) {
    echo "❌ เกิดข้อผิดพลาด: " . $e->getMessage() . "\n";
    echo "กรุณาตรวจสอบว่าเซิร์ฟเวอร์ทำงานอยู่และ endpoint ถูกต้อง\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🔗 สำหรับการทดสอบใน browser:\n";
echo "   URL: {$baseUrl}\n";
echo "   Method: GET\n";
echo "   Parameters: startTime, endTime (optional)\n"; 