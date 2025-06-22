<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Client;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 ตรวจสอบและแก้ไข client_status ของผู้ใช้\n";
echo "==========================================\n\n";

// ตรวจสอบผู้ใช้ทั้ง 3 คน
$emails = [
    'hamsftmo@gmail.com',
    'Janischa.trade@gmail.com', 
    'kantapong0592@gmail.com'
];

foreach ($emails as $email) {
    echo "📧 ตรวจสอบผู้ใช้: {$email}\n";
    
    $user = User::where('email', $email)->first();
    
    if (!$user) {
        echo "   ❌ ไม่พบผู้ใช้\n\n";
        continue;
    }
    
    echo "   ✅ พบผู้ใช้: {$user->name} (ID: {$user->id})\n";
    
    // ตรวจสอบข้อมูลลูกค้าที่เกี่ยวข้อง
    $clients = Client::where('partner_account', $user->id)->get();
    
    if ($clients->isEmpty()) {
        echo "   ⚠️  ไม่พบข้อมูลลูกค้าที่เกี่ยวข้อง\n";
        
        // ลองค้นหาด้วย partner_account ที่เป็น string
        $clients = Client::where('partner_account', (string)$user->id)->get();
        
        if ($clients->isEmpty()) {
            echo "   ❌ ไม่พบข้อมูลลูกค้าในรูปแบบใดๆ\n\n";
            continue;
        }
    }
    
    echo "   📊 พบลูกค้า {$clients->count()} รายการ\n";
    
    // แสดงสถิติ client_status ปัจจุบัน
    $statusCounts = $clients->groupBy('client_status')->map->count();
    echo "   📈 สถิติ client_status ปัจจุบัน:\n";
    foreach ($statusCounts as $status => $count) {
        echo "      - {$status}: {$count} รายการ\n";
    }
    
    // ตรวจสอบข้อมูลจาก raw_data เพื่อหาค่า client_status ที่ถูกต้อง
    $correctStatuses = [];
    foreach ($clients as $client) {
        if (isset($client->raw_data['client_status'])) {
            $correctStatuses[] = $client->raw_data['client_status'];
        }
    }
    
    if (!empty($correctStatuses)) {
        echo "   🔍 พบ client_status ใน raw_data:\n";
        $uniqueStatuses = array_unique($correctStatuses);
        foreach ($uniqueStatuses as $status) {
            $count = count(array_filter($correctStatuses, function($s) use ($status) { return $s === $status; }));
            echo "      - {$status}: {$count} รายการ\n";
        }
        
        // อัพเดต client_status ให้ตรงกับ raw_data
        $updatedCount = 0;
        foreach ($clients as $client) {
            if (isset($client->raw_data['client_status']) && 
                $client->raw_data['client_status'] !== $client->client_status) {
                
                $oldStatus = $client->client_status;
                $newStatus = $client->raw_data['client_status'];
                
                $client->update(['client_status' => $newStatus]);
                $updatedCount++;
                
                echo "      🔄 อัพเดต client_uid: {$client->client_uid} จาก {$oldStatus} เป็น {$newStatus}\n";
            }
        }
        
        if ($updatedCount > 0) {
            echo "   ✅ อัพเดต client_status เรียบร้อย {$updatedCount} รายการ\n";
        } else {
            echo "   ℹ️  client_status ถูกต้องแล้ว ไม่ต้องอัพเดต\n";
        }
    } else {
        echo "   ⚠️  ไม่พบ client_status ใน raw_data\n";
    }
    
    echo "\n";
}

echo "🎯 สรุปการตรวจสอบและแก้ไขเสร็จสิ้น!\n";
echo "=====================================\n";

// แสดงสถิติรวม
echo "\n📊 สถิติรวมทั้งหมด:\n";
$totalStatusCounts = Client::selectRaw('client_status, COUNT(*) as count')
    ->groupBy('client_status')
    ->get()
    ->pluck('count', 'client_status')
    ->toArray();

foreach ($totalStatusCounts as $status => $count) {
    echo "   - {$status}: {$count} รายการ\n";
}

echo "\n✅ ตรวจสอบและแก้ไข client_status เสร็จสิ้นแล้ว!\n"; 