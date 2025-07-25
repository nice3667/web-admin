<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Client;

echo "🔍 ตรวจสอบข้อมูลลูกค้า\n";
echo "=====================\n\n";

// ตรวจสอบผู้ใช้
$users = User::whereIn('email', ['hamsftmo@gmail.com', 'Janischa.trade@gmail.com'])->get();

foreach ($users as $user) {
    echo "📧 ผู้ใช้: {$user->name} ({$user->email}) - ID: {$user->id}\n";
}

echo "\n📊 ข้อมูลลูกค้าทั้งหมด:\n";
$clients = Client::all();

echo "จำนวนลูกค้าทั้งหมด: " . $clients->count() . "\n";

// ตรวจสอบ partner_account ที่มีอยู่
$partnerAccounts = $clients->pluck('partner_account')->unique()->filter();
echo "Partner Accounts ที่มี: " . $partnerAccounts->implode(', ') . "\n";

// ตรวจสอบ client_status ที่มีอยู่
$statuses = $clients->pluck('client_status')->unique();
echo "Client Status ที่มี: " . $statuses->implode(', ') . "\n";

// แสดงตัวอย่างข้อมูลลูกค้า
echo "\n📋 ตัวอย่างข้อมูลลูกค้า (5 รายการแรก):\n";
$sampleClients = $clients->take(5);
foreach ($sampleClients as $client) {
    echo "- Client UID: {$client->client_uid}, Partner Account: {$client->partner_account}, Status: {$client->client_status}\n";
}

echo "\n✅ ตรวจสอบเสร็จสิ้น\n"; 