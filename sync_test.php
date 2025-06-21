<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Data Sync for All Reports ===\n\n";

// Test Report (Janischa) sync
echo "1. Testing Report Data Sync (Janischa account)...\n";
try {
    $exitCode = Artisan::call('sync:report-data', ['--new-only' => true]);
    echo "   Result: " . ($exitCode === 0 ? "SUCCESS" : "FAILED") . "\n";
    echo "   Output: " . Artisan::output() . "\n";
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test Report1 (Ham) sync
echo "2. Testing Report1 Data Sync (Ham account)...\n";
try {
    $exitCode = Artisan::call('sync:report1-data', ['--new-only' => true]);
    echo "   Result: " . ($exitCode === 0 ? "SUCCESS" : "FAILED") . "\n";
    echo "   Output: " . Artisan::output() . "\n";
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test Report2 (Kantapong) sync
echo "3. Testing Report2 Data Sync (Kantapong account)...\n";
try {
    $exitCode = Artisan::call('sync:report2-data', ['--new-only' => true]);
    echo "   Result: " . ($exitCode === 0 ? "SUCCESS" : "FAILED") . "\n";
    echo "   Output: " . Artisan::output() . "\n";
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Check table counts
echo "4. Checking table counts...\n";
try {
    $clientsCount = DB::table('clients')->count();
    echo "   clients table: {$clientsCount} records\n";
    
    if (DB::getSchemaBuilder()->hasTable('ham_clients')) {
        $hamClientsCount = DB::table('ham_clients')->count();
        echo "   ham_clients table: {$hamClientsCount} records\n";
    } else {
        echo "   ham_clients table: Not created yet\n";
    }
    
    if (DB::getSchemaBuilder()->hasTable('kantapong_clients')) {
        $kantapongClientsCount = DB::table('kantapong_clients')->count();
        echo "   kantapong_clients table: {$kantapongClientsCount} records\n";
    } else {
        echo "   kantapong_clients table: Not created yet\n";
    }
    
} catch (Exception $e) {
    echo "   Error checking tables: " . $e->getMessage() . "\n";
}

echo "\n=== Sync Test Complete ===\n"; 