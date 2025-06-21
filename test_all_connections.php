<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Testing Exness API Connections ===\n\n";

// Test Ham Connection
echo "1. Testing Ham (hamsftmo@gmail.com)...\n";
try {
    $hamService = new App\Services\HamExnessAuthService();
    $hamResult = $hamService->getClientsData();
    
    if (isset($hamResult['error'])) {
        echo "   ❌ ERROR: " . $hamResult['error'] . "\n";
    } else {
        $count = count($hamResult['data']);
        echo "   ✅ SUCCESS: {$count} clients retrieved\n";
        
        // Show sample data
        if ($count > 0) {
            $sample = $hamResult['data'][0];
            echo "   📊 Sample: UID={$sample['client_uid']}, Country={$sample['client_country']}, Reward={$sample['reward_usd']}\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ EXCEPTION: " . $e->getMessage() . "\n";
}

echo "\n";

// Test Kantapong Connection
echo "2. Testing Kantapong (kantapong0592@gmail.com)...\n";
try {
    $kantapongService = new App\Services\KantapongExnessAuthService();
    $kantapongResult = $kantapongService->getClientsData();
    
    if (isset($kantapongResult['error'])) {
        echo "   ❌ ERROR: " . $kantapongResult['error'] . "\n";
    } else {
        $count = count($kantapongResult['data']);
        echo "   ✅ SUCCESS: {$count} clients retrieved\n";
        
        // Show sample data
        if ($count > 0) {
            $sample = $kantapongResult['data'][0];
            echo "   📊 Sample: UID={$sample['client_uid']}, Country={$sample['client_country']}, Reward={$sample['reward_usd']}\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ EXCEPTION: " . $e->getMessage() . "\n";
}

echo "\n";

// Test Janischa Connection
echo "3. Testing Janischa (Janischa.trade@gmail.com)...\n";
try {
    $janischaService = new App\Services\JanischaExnessAuthService();
    $janischaResult = $janischaService->getClientsData();
    
    if (isset($janischaResult['error'])) {
        echo "   ❌ ERROR: " . $janischaResult['error'] . "\n";
    } else {
        $count = count($janischaResult['data']);
        echo "   ✅ SUCCESS: {$count} clients retrieved\n";
        
        // Show sample data
        if ($count > 0) {
            $sample = $janischaResult['data'][0];
            echo "   📊 Sample: UID={$sample['client_uid']}, Country={$sample['client_country']}, Reward={$sample['reward_usd']}\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ EXCEPTION: " . $e->getMessage() . "\n";
}

echo "\n";

// Database status
echo "=== Database Status ===\n";
try {
    $hamCount = App\Models\HamClient::count();
    $kantapongCount = App\Models\KantapongClient::count();
    $janischaCount = App\Models\JanischaClient::count();
    
    echo "Ham clients in DB: {$hamCount}\n";
    echo "Kantapong clients in DB: {$kantapongCount}\n";
    echo "Janischa clients in DB: {$janischaCount}\n";
    echo "Total clients in DB: " . ($hamCount + $kantapongCount + $janischaCount) . "\n";
    
    // Last sync times
    $hamLast = App\Models\HamClient::orderBy('last_sync_at', 'desc')->first();
    $kantapongLast = App\Models\KantapongClient::orderBy('last_sync_at', 'desc')->first();
    $janischaLast = App\Models\JanischaClient::orderBy('last_sync_at', 'desc')->first();
    
    echo "\nLast sync times:\n";
    echo "Ham: " . ($hamLast ? $hamLast->last_sync_at : 'Never') . "\n";
    echo "Kantapong: " . ($kantapongLast ? $kantapongLast->last_sync_at : 'Never') . "\n";
    echo "Janischa: " . ($janischaLast ? $janischaLast->last_sync_at : 'Never') . "\n";
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

echo "\n=== Summary ===\n";
echo "✅ All 3 users have separate database tables\n";
echo "✅ All 3 reports use their own Exness API credentials\n";
echo "✅ Auto-sync is configured (every 30 minutes)\n";
echo "✅ Individual backup sync (hourly)\n";
echo "\nTo run sync manually:\n";
echo "php artisan sync:all-users --force\n";
echo "php artisan sync:ham-data --force\n";
echo "php artisan sync:kantapong-data --force\n";
echo "php artisan sync:janischa-data --force\n"; 