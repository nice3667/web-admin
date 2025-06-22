<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Client;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "🔍 ตรวจสอบและอัพเดต user_id ในตาราง clients\n";
echo "==========================================\n\n";

// ตรวจสอบโครงสร้างตาราง
echo "📊 ตรวจสอบโครงสร้างตาราง clients:\n";
$columns = Schema::getColumnListing('clients');
foreach ($columns as $column) {
    echo "- {$column}\n";
}

echo "\n";

// ตรวจสอบว่ามี user_id field หรือไม่
if (in_array('user_id', $columns)) {
    echo "✅ ตาราง clients มี user_id field อยู่แล้ว\n";
} else {
    echo "❌ ตาราง clients ไม่มี user_id field\n";
    echo "🔧 เพิ่ม user_id field...\n";
    
    Schema::table('clients', function ($table) {
        $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('cascade');
    });
    
    echo "✅ เพิ่ม user_id field เรียบร้อย\n";
}

echo "\n";

// อ่านข้อมูลจาก backup
echo "📂 อ่านข้อมูลจาก backup...\n";
$backupFile = storage_path('backups/clients_backup_2025-06-19_19-21-52.json');

if (!file_exists($backupFile)) {
    echo "❌ ไม่พบไฟล์ backup\n";
    exit(1);
}

$backupData = json_decode(file_get_contents($backupFile), true);

if (!$backupData) {
    echo "❌ ไม่สามารถอ่านข้อมูล backup ได้\n";
    exit(1);
}

echo "✅ อ่านข้อมูล backup เรียบร้อย (" . count($backupData) . " รายการ)\n";

// สร้าง map ของ client_uid กับ user_id
$clientUserMap = [];
foreach ($backupData as $client) {
    if (isset($client['client_uid']) && isset($client['user_id'])) {
        $clientUserMap[$client['client_uid']] = $client['user_id'];
    }
}

echo "📋 สร้าง map ข้อมูล: " . count($clientUserMap) . " รายการ\n";

// อัพเดตข้อมูลในตาราง clients
echo "\n🔄 อัพเดตข้อมูลในตาราง clients...\n";
$updatedCount = 0;
$notFoundCount = 0;

foreach ($clientUserMap as $clientUid => $userId) {
    $result = DB::table('clients')
        ->where('client_uid', $clientUid)
        ->update(['user_id' => $userId]);
    
    if ($result > 0) {
        $updatedCount++;
    } else {
        $notFoundCount++;
    }
}

echo "✅ อัพเดตเรียบร้อย: {$updatedCount} รายการ\n";
echo "❌ ไม่พบในตาราง: {$notFoundCount} รายการ\n";

// ตรวจสอบผลลัพธ์
echo "\n📊 ตรวจสอบผลลัพธ์:\n";

$users = User::whereIn('email', ['hamsftmo@gmail.com', 'Janischa.trade@gmail.com', 'kantapong0592@gmail.com'])->get();

foreach ($users as $user) {
    echo "\n📧 ผู้ใช้: {$user->name} ({$user->email}) - ID: {$user->id}\n";
    
    $clients = Client::where('user_id', $user->id)->get();
    
    if ($clients->isEmpty()) {
        echo "   ⚠️  ไม่พบข้อมูลลูกค้า\n";
        continue;
    }
    
    echo "   📊 พบลูกค้า {$clients->count()} รายการ\n";
    
    // แสดงสถิติ client_status
    $statusCounts = $clients->groupBy('client_status')->map->count();
    echo "   📈 สถิติ client_status:\n";
    foreach ($statusCounts as $status => $count) {
        echo "      - {$status}: {$count} รายการ\n";
    }
}

echo "\n✅ ตรวจสอบและอัพเดตเสร็จสิ้น!\n"; 