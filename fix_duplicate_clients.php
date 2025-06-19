<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== การแก้ไขข้อมูลซ้ำในตาราง clients ===\n\n";

// 1. แสดงสถานะปัจจุบัน
$totalBefore = App\Models\Client::count();
$uniqueBefore = App\Models\Client::distinct('client_uid')->count();
$duplicateBefore = $totalBefore - $uniqueBefore;

echo "สถานะก่อนแก้ไข:\n";
echo "- จำนวนบัญชีทั้งหมด: {$totalBefore}\n";
echo "- จำนวน client_uid ที่ไม่ซ้ำ: {$uniqueBefore}\n";
echo "- จำนวนข้อมูลซ้ำ: {$duplicateBefore}\n\n";

if ($duplicateBefore == 0) {
    echo "✅ ไม่พบข้อมูลซ้ำ ไม่จำเป็นต้องแก้ไข\n";
    exit;
}

// 2. ค้นหาข้อมูลซ้ำ
echo "กำลังค้นหาข้อมูลซ้ำ...\n";
$duplicates = Illuminate\Support\Facades\DB::select('
    SELECT client_uid, COUNT(*) as count 
    FROM clients 
    GROUP BY client_uid 
    HAVING COUNT(*) > 1
    ORDER BY count DESC
');

echo "พบข้อมูลซ้ำ " . count($duplicates) . " รายการ:\n";
foreach ($duplicates as $dup) {
    echo "- {$dup->client_uid} ({$dup->count} records)\n";
}

// 3. ถามยืนยันก่อนลบ
echo "\n" . str_repeat("=", 50) . "\n";
echo "⚠️  คำเตือน: การดำเนินการนี้จะลบข้อมูลซ้ำอย่างถาวร\n";
echo "📋 วิธีการลบ: เก็บข้อมูลที่ล่าสุด (ID สูงสุด) ลบข้อมูลเก่า\n";
echo "💾 แนะนำ: สำรองข้อมูลก่อนดำเนินการ\n";
echo str_repeat("=", 50) . "\n";

echo "คุณต้องการดำเนินการลบข้อมูลซ้ำหรือไม่? (y/n): ";
$handle = fopen("php://stdin", "r");
$confirm = trim(fgets($handle));
fclose($handle);

if (strtolower($confirm) !== 'y') {
    echo "❌ ยกเลิกการดำเนินการ\n";
    exit;
}

// 4. สร้าง backup ก่อนลบ
echo "\n📦 กำลังสร้าง backup...\n";
$backupFileName = 'storage/backups/clients_before_cleanup_' . date('Y-m-d_H-i-s') . '.json';

// สร้าง directory หากไม่มี
if (!file_exists('storage/backups')) {
    mkdir('storage/backups', 0755, true);
}

$allClients = App\Models\Client::all()->toArray();
file_put_contents($backupFileName, json_encode($allClients, JSON_PRETTY_PRINT));
echo "✅ Backup สร้างแล้ว: {$backupFileName}\n";

// 5. ลบข้อมูลซ้ำ
echo "\n🧹 กำลังลบข้อมูลซ้ำ...\n";
$deletedCount = 0;

foreach ($duplicates as $dup) {
    $clientUid = $dup->client_uid;
    
    // หา records ที่ซ้ำสำหรับ client_uid นี้
    $clientRecords = App\Models\Client::where('client_uid', $clientUid)
        ->orderBy('id', 'desc')
        ->get();
    
    // เก็บ record แรก (ID สูงสุด = ล่าสุด) ลบที่เหลือ
    $keepRecord = $clientRecords->first();
    $recordsToDelete = $clientRecords->skip(1);
    
    echo "  - {$clientUid}: เก็บ ID {$keepRecord->id}, ลบ " . $recordsToDelete->count() . " records\n";
    
    foreach ($recordsToDelete as $record) {
        $record->delete();
        $deletedCount++;
    }
}

// 6. ตรวจสอบผลลัพธ์
echo "\n📊 ผลลัพธ์การแก้ไข:\n";
$totalAfter = App\Models\Client::count();
$uniqueAfter = App\Models\Client::distinct('client_uid')->count();
$duplicateAfter = $totalAfter - $uniqueAfter;

echo "สถานะหลังแก้ไข:\n";
echo "- จำนวนบัญชีทั้งหมด: {$totalAfter} (ลดลง " . ($totalBefore - $totalAfter) . ")\n";
echo "- จำนวน client_uid ที่ไม่ซ้ำ: {$uniqueAfter}\n";
echo "- จำนวนข้อมูลซ้ำ: {$duplicateAfter}\n";
echo "- จำนวน records ที่ลบ: {$deletedCount}\n";

if ($duplicateAfter == 0) {
    echo "\n✅ แก้ไขข้อมูลซ้ำเสร็จสิ้น! ไม่มีข้อมูลซ้ำแล้ว\n";
    echo "🎉 จำนวนบัญชีถูกต้องแล้ว: {$totalAfter} บัญชี\n";
} else {
    echo "\n⚠️  ยังคงมีข้อมูลซ้ำอยู่ อาจต้องตรวจสอบเพิ่มเติม\n";
}

// 7. แนะนำขั้นตอนถัดไป
echo "\n" . str_repeat("=", 50) . "\n";
echo "📝 ขั้นตอนถัดไป:\n";
echo "1. ตรวจสอบผลลัพธ์ในระบบ\n";
echo "2. หากพบปัญหา สามารถ restore จาก backup ได้\n";
echo "3. ลบไฟล์ backup เมื่อมั่นใจว่าข้อมูลถูกต้องแล้ว\n";
echo "4. รัน sync ใหม่เพื่อตรวจสอบว่าปัญหาไม่เกิดซ้ำ\n";
echo str_repeat("=", 50) . "\n"; 