<?php

require_once 'vendor/autoload.php';

use App\Services\JanischaExnessAuthService;
use App\Models\JanischaClient;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔄 Starting Janischa client data sync...\n";
echo "=====================================\n\n";

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

    echo "📊 Received " . count($clients) . " clients from API\n";
    echo "🔄 Processing clients...\n";

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

        // Show progress every 50 clients
        if ($processed % 50 == 0) {
            echo "   Processed: {$processed}/" . count($clients) . "\n";
        }
    }

    echo "\n📈 SYNC RESULTS\n";
    echo "===============\n";
    echo "✅ Total processed: {$processed}\n";
    echo "🆕 New clients: {$created}\n";
    echo "🔄 Updated clients: {$updated}\n";
    echo "📊 API clients: " . count($clients) . "\n";
    echo "⏰ Completed at: " . now()->format('Y-m-d H:i:s') . "\n";

    // Show database statistics
    $totalInDb = JanischaClient::count();
    $activeClients = JanischaClient::where('client_status', 'ACTIVE')->count();
    $inactiveClients = JanischaClient::where('client_status', 'INACTIVE')->count();

    echo "\n📊 DATABASE STATISTICS\n";
    echo "=====================\n";
    echo "📁 Total in database: {$totalInDb}\n";
    echo "✅ Active clients: {$activeClients}\n";
    echo "❌ Inactive clients: {$inactiveClients}\n";

    // Show recent clients
    $recentClients = JanischaClient::orderBy('created_at', 'desc')
        ->limit(5)
        ->get(['client_uid', 'client_status', 'reg_date', 'created_at']);

    if ($recentClients->count() > 0) {
        echo "\n🆕 RECENT CLIENTS\n";
        echo "=================\n";
        foreach ($recentClients as $client) {
            echo "   • {$client->client_uid} ({$client->client_status}) - {$client->reg_date}\n";
        }
    }

    // Log the results
    Log::info('Janischa sync completed', [
        'processed' => $processed,
        'created' => $created,
        'updated' => $updated,
        'total_api_clients' => count($clients),
        'total_in_db' => $totalInDb
    ]);

    echo "\n✅ Janischa sync completed successfully!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    Log::error('Janischa sync failed', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
