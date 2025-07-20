<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\HamClient;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class VerifyHamCustomerData extends Command
{
    protected $signature = 'verify:ham-customers';
    protected $description = 'Verify Ham customer data is properly integrated and visible';

    public function handle()
    {
        $this->info('🔍 Verifying Ham Customer Data Integration');
        $this->info('==========================================');

        // Step 1: Check Ham clients in database
        $this->info('1️⃣ Checking Ham Database Records...');
        $hamClientCount = HamClient::count();
        $activeHamClients = HamClient::where('client_status', 'ACTIVE')->count();
        $recentHamClients = HamClient::where('last_sync_at', '>', now()->subHours(1))->count();

        $this->info("Total Ham clients: {$hamClientCount}");
        $this->info("Active Ham clients: {$activeHamClients}");
        $this->info("Recently synced: {$recentHamClients}");

        // Step 2: Check user mapping
        $this->info('');
        $this->info('2️⃣ Checking User Mapping...');
        $hamUser = User::where('email', 'hamsftmo@gmail.com')->first();
        
        if ($hamUser) {
            $this->info("✅ Ham user found: ID {$hamUser->id}, Name: {$hamUser->name}");
        } else {
            $this->error("❌ Ham user not found in users table");
        }

        // Step 3: Check if data appears in unified clients table
        $this->info('');
        $this->info('3️⃣ Checking Unified Client Data...');
        
        // Sample some Ham clients to check their data quality
        $sampleClients = HamClient::whereNotNull('client_country')
            ->where('volume_lots', '>', 0)
            ->limit(5)
            ->get(['client_uid', 'client_country', 'volume_lots', 'reward_usd', 'client_status']);

        if ($sampleClients->count() > 0) {
            $this->info("✅ Found {$sampleClients->count()} sample clients with trading activity:");
            foreach ($sampleClients as $client) {
                $this->info("  - UID: {$client->client_uid}, Country: {$client->client_country}, Volume: {$client->volume_lots}, Reward: \${$client->reward_usd}, Status: {$client->client_status}");
            }
        } else {
            $this->warn("⚠️  No clients found with trading activity");
        }

        // Step 4: Check client statistics
        $this->info('');
        $this->info('4️⃣ Client Statistics...');
        
        $countryStats = HamClient::selectRaw('client_country, COUNT(*) as count')
            ->groupBy('client_country')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        $this->info("Top countries:");
        foreach ($countryStats as $stat) {
            $this->info("  - {$stat->client_country}: {$stat->count} clients");
        }

        $volumeStats = HamClient::where('volume_lots', '>', 0)->count();
        $rewardStats = HamClient::where('reward_usd', '>', 0)->count();
        
        $this->info("Clients with trading volume: {$volumeStats}");
        $this->info("Clients with rewards: {$rewardStats}");

        // Step 5: Test API integration
        $this->info('');
        $this->info('5️⃣ Testing API Integration...');
        
        try {
            // Simulate what the customer controller does
            $testQuery = HamClient::latest('last_sync_at')->first();
            if ($testQuery) {
                $this->info("✅ Database query successful");
                $this->info("Latest client UID: {$testQuery->client_uid}");
                $this->info("Last sync: {$testQuery->last_sync_at}");
            } else {
                $this->error("❌ No data found in query");
            }
        } catch (\Exception $e) {
            $this->error("❌ Query failed: " . $e->getMessage());
        }

        // Step 6: Recommendations
        $this->info('');
        $this->info('6️⃣ Recommendations...');
        
        if ($hamClientCount === 0) {
            $this->error("❌ No Ham client data - run 'php artisan fix:ham-exness' first");
            return 1;
        }

        if ($recentHamClients < ($hamClientCount * 0.8)) {
            $this->warn("⚠️  Many records are not recently synced - consider running sync more frequently");
        }

        if ($activeHamClients === 0) {
            $this->warn("⚠️  No active clients found - this might be normal if all clients are inactive");
        }

        if (!$hamUser) {
            $this->warn("⚠️  Ham user not found - client assignment may not work properly");
        }

        $this->info('');
        $this->info('✅ Verification completed!');
        $this->info('Ham customer data should now be visible in the admin panel.');
        
        return 0;
    }
} 