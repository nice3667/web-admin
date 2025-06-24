<?php

require_once 'vendor/autoload.php';

use App\Services\JanischaExnessAuthService;
use App\Services\HamExnessAuthService;
use App\Services\KantapongExnessAuthService;
use App\Models\JanischaClient;
use App\Models\HamClient;
use App\Models\KantapongClient;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔄 Starting sync for all client accounts...\n";
echo "==========================================\n\n";

$results = [];

// 1. Sync Janischa Data
echo "📊 Syncing Janischa data...\n";
try {
    $janischaService = new JanischaExnessAuthService();
    $apiResponse = $janischaService->getClientsData();

    if (isset($apiResponse['error'])) {
        throw new Exception($apiResponse['error']);
    }

    $clients = $apiResponse['data'] ?? [];
    $processed = 0;
    $created = 0;
    $updated = 0;

    foreach ($clients as $clientData) {
        $clientUid = $clientData['client_uid'] ?? null;
        if (!$clientUid) continue;

        $dbData = [
            'partner_account' => $clientData['partner_account'] ?? null,
            'client_uid' => $clientUid,
            'client_id' => $clientData['client_id'] ?? null,
            'reg_date' => isset($clientData['reg_date']) ? Carbon::parse($clientData['reg_date'])->format('Y-m-d') : null,
            'client_country' => $clientData['client_country'] ?? $clientData['country'] ?? null,
            'volume_lots' => floatval($clientData['volume_lots'] ?? 0),
            'volume_mln_usd' => floatval($clientData['volume_mln_usd'] ?? 0),
            'reward_usd' => floatval($clientData['reward_usd'] ?? 0),
            'client_status' => $clientData['client_status'] ?? 'UNKNOWN',
            'kyc_passed' => (bool)($clientData['kyc_passed'] ?? false),
            'ftd_received' => (bool)($clientData['ftd_received'] ?? false),
            'ftt_made' => (bool)($clientData['ftt_made'] ?? false),
            'raw_data' => $clientData,
            'last_sync_at' => now(),
        ];

        $client = JanischaClient::updateOrCreate(
            ['client_uid' => $clientUid],
            $dbData
        );

        if ($client->wasRecentlyCreated) {
            $created++;
        } else {
            $updated++;
        }
        $processed++;
    }

    $results['janischa'] = [
        'success' => true,
        'processed' => $processed,
        'created' => $created,
        'updated' => $updated,
        'total_api' => count($clients)
    ];

    echo "✅ Janischa: {$processed} processed, {$created} created, {$updated} updated\n";

} catch (Exception $e) {
    $results['janischa'] = ['success' => false, 'error' => $e->getMessage()];
    echo "❌ Janischa: " . $e->getMessage() . "\n";
}

// 2. Sync Ham Data
echo "\n📊 Syncing Ham data...\n";
try {
    $hamService = new HamExnessAuthService();
    $apiResponse = $hamService->getClientsData();

    if (isset($apiResponse['error'])) {
        throw new Exception($apiResponse['error']);
    }

    $clients = $apiResponse['data'] ?? [];
    $processed = 0;
    $created = 0;
    $updated = 0;

    foreach ($clients as $clientData) {
        $clientUid = $clientData['client_uid'] ?? null;
        if (!$clientUid) continue;

        $dbData = [
            'partner_account' => $clientData['partner_account'] ?? null,
            'client_uid' => $clientUid,
            'client_id' => $clientData['client_id'] ?? null,
            'reg_date' => isset($clientData['reg_date']) ? Carbon::parse($clientData['reg_date'])->format('Y-m-d') : null,
            'client_country' => $clientData['client_country'] ?? $clientData['country'] ?? null,
            'volume_lots' => floatval($clientData['volume_lots'] ?? 0),
            'volume_mln_usd' => floatval($clientData['volume_mln_usd'] ?? 0),
            'reward_usd' => floatval($clientData['reward_usd'] ?? 0),
            'client_status' => $clientData['client_status'] ?? 'UNKNOWN',
            'kyc_passed' => (bool)($clientData['kyc_passed'] ?? false),
            'ftd_received' => (bool)($clientData['ftd_received'] ?? false),
            'ftt_made' => (bool)($clientData['ftt_made'] ?? false),
            'raw_data' => $clientData,
            'last_sync_at' => now(),
        ];

        $client = HamClient::updateOrCreate(
            ['client_uid' => $clientUid],
            $dbData
        );

        if ($client->wasRecentlyCreated) {
            $created++;
        } else {
            $updated++;
        }
        $processed++;
    }

    $results['ham'] = [
        'success' => true,
        'processed' => $processed,
        'created' => $created,
        'updated' => $updated,
        'total_api' => count($clients)
    ];

    echo "✅ Ham: {$processed} processed, {$created} created, {$updated} updated\n";

} catch (Exception $e) {
    $results['ham'] = ['success' => false, 'error' => $e->getMessage()];
    echo "❌ Ham: " . $e->getMessage() . "\n";
}

// 3. Sync Kantapong Data
echo "\n📊 Syncing Kantapong data...\n";
try {
    $kantapongService = new KantapongExnessAuthService();
    $apiResponse = $kantapongService->getClientsData();

    if (isset($apiResponse['error'])) {
        throw new Exception($apiResponse['error']);
    }

    $clients = $apiResponse['data'] ?? [];
    $processed = 0;
    $created = 0;
    $updated = 0;

    foreach ($clients as $clientData) {
        $clientUid = $clientData['client_uid'] ?? null;
        if (!$clientUid) continue;

        $dbData = [
            'partner_account' => $clientData['partner_account'] ?? null,
            'client_uid' => $clientUid,
            'client_id' => $clientData['client_id'] ?? null,
            'reg_date' => isset($clientData['reg_date']) ? Carbon::parse($clientData['reg_date'])->format('Y-m-d') : null,
            'client_country' => $clientData['client_country'] ?? $clientData['country'] ?? null,
            'volume_lots' => floatval($clientData['volume_lots'] ?? 0),
            'volume_mln_usd' => floatval($clientData['volume_mln_usd'] ?? 0),
            'reward_usd' => floatval($clientData['reward_usd'] ?? 0),
            'client_status' => $clientData['client_status'] ?? 'UNKNOWN',
            'kyc_passed' => (bool)($clientData['kyc_passed'] ?? false),
            'ftd_received' => (bool)($clientData['ftd_received'] ?? false),
            'ftt_made' => (bool)($clientData['ftt_made'] ?? false),
            'raw_data' => $clientData,
            'last_sync_at' => now(),
        ];

        $client = KantapongClient::updateOrCreate(
            ['client_uid' => $clientUid],
            $dbData
        );

        if ($client->wasRecentlyCreated) {
            $created++;
        } else {
            $updated++;
        }
        $processed++;
    }

    $results['kantapong'] = [
        'success' => true,
        'processed' => $processed,
        'created' => $created,
        'updated' => $updated,
        'total_api' => count($clients)
    ];

    echo "✅ Kantapong: {$processed} processed, {$created} created, {$updated} updated\n";

} catch (Exception $e) {
    $results['kantapong'] = ['success' => false, 'error' => $e->getMessage()];
    echo "❌ Kantapong: " . $e->getMessage() . "\n";
}

// Summary
echo "\n📈 SYNC SUMMARY\n";
echo "===============\n";

$totalProcessed = 0;
$totalCreated = 0;
$totalUpdated = 0;
$totalApiClients = 0;

foreach ($results as $account => $result) {
    if ($result['success']) {
        $totalProcessed += $result['processed'];
        $totalCreated += $result['created'];
        $totalUpdated += $result['updated'];
        $totalApiClients += $result['total_api'];

        echo "✅ {$account}: {$result['processed']} processed ({$result['created']} new, {$result['updated']} updated)\n";
    } else {
        echo "❌ {$account}: Failed - {$result['error']}\n";
    }
}

echo "\n🎯 TOTAL: {$totalProcessed} processed, {$totalCreated} new clients, {$totalUpdated} updated\n";
echo "📊 API Clients: {$totalApiClients}\n";
echo "⏰ Completed at: " . now()->format('Y-m-d H:i:s') . "\n";

// Log the results
Log::info('All clients sync completed', [
    'results' => $results,
    'total_processed' => $totalProcessed,
    'total_created' => $totalCreated,
    'total_updated' => $totalUpdated,
    'total_api_clients' => $totalApiClients
]);

echo "\n✅ All sync operations completed!\n";
