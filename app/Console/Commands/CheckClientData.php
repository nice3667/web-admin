<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Services\ClientService;
use Illuminate\Support\Facades\Log;

class CheckClientData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clients:check-data 
                            {--compare-api : Compare with API data}
                            {--show-details : Show detailed client information}
                            {--limit=10 : Number of clients to show}
                            {--api-summary : Show API summary only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and compare client data between database and API';

    protected $clientService;

    public function __construct(ClientService $clientService)
    {
        parent::__construct();
        $this->clientService = $clientService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Client Data Check Tool');
        $this->info('========================');

        // Show database summary
        $this->showDatabaseSummary();

        // Show API summary only (new option)
        if ($this->option('api-summary')) {
            $this->showApiSummary();
            return 0;
        }

        // Show sample data
        if ($this->option('show-details')) {
            $this->showDetailedData();
        }

        // Compare with API if requested
        if ($this->option('compare-api')) {
            $this->compareWithApi();
        }

        return 0;
    }

    /**
     * Show database summary
     */
    private function showDatabaseSummary()
    {
        $this->info('📊 Database Summary:');
        
        $totalClients = Client::count();
        $totalVolumeLots = Client::sum('volume_lots');
        $totalVolumeUsd = Client::sum('volume_mln_usd');
        $totalReward = Client::sum('reward_usd');
        $totalRebate = Client::sum('rebate_amount_usd');
        
        $this->line("   • Total Clients: {$totalClients}");
        $this->line("   • Total Volume (Lots): " . number_format($totalVolumeLots, 2));
        $this->line("   • Total Volume (USD): " . number_format($totalVolumeUsd, 2));
        $this->line("   • Total Reward (USD): " . number_format($totalReward, 2));
        $this->line("   • Total Rebate (USD): " . number_format($totalRebate, 2));
        
        // Status distribution
        $statusCounts = Client::selectRaw('client_status, COUNT(*) as count')
            ->groupBy('client_status')
            ->get()
            ->pluck('count', 'client_status')
            ->toArray();
        
        $this->line("   • Status Distribution:");
        foreach ($statusCounts as $status => $count) {
            $this->line("     - {$status}: {$count}");
        }
        
        $this->line('');
    }

    /**
     * Show detailed client data
     */
    private function showDetailedData()
    {
        $limit = (int) $this->option('limit');
        
        $this->info("📋 Sample Client Data (showing {$limit} records):");
        $this->line(str_repeat('-', 100));
        $this->line(sprintf('%-15s %-10s %-15s %-15s %-15s %-10s', 'Client UID', 'Status', 'Volume Lots', 'Volume USD', 'Reward USD', 'Rebate USD'));
        $this->line(str_repeat('-', 100));
        
        $clients = Client::select('client_uid', 'client_status', 'volume_lots', 'volume_mln_usd', 'reward_usd', 'rebate_amount_usd', 'reg_date', 'partner_account', 'client_id')
            ->orderBy('volume_lots', 'desc')
            ->limit($limit)
            ->get();
        
        foreach ($clients as $client) {
            $this->line(sprintf(
                '%-15s %-10s %-15s %-15s %-15s %-10s',
                substr($client->client_uid, 0, 12) . '...',
                $client->client_status,
                number_format($client->volume_lots, 2),
                number_format($client->volume_mln_usd, 2),
                number_format($client->reward_usd, 2),
                number_format($client->rebate_amount_usd, 2)
            ));
        }
        
        $this->line(str_repeat('-', 100));
        $this->line('');
        
        // Show duplicate UIDs analysis
        $this->showDuplicateUidsAnalysis();
    }

    /**
     * Show analysis of duplicate client UIDs
     */
    private function showDuplicateUidsAnalysis()
    {
        $this->info('🔍 Duplicate Client UIDs Analysis:');
        
        // Get all client UIDs and count occurrences
        $uidCounts = Client::selectRaw('client_uid, COUNT(*) as count')
            ->groupBy('client_uid')
            ->having('count', '>', 1)
            ->orderBy('count', 'desc')
            ->get();
        
        if ($uidCounts->isEmpty()) {
            $this->line('   ✅ No duplicate UIDs found');
            return;
        }
        
        $this->line("   • Found {$uidCounts->count()} UIDs with multiple accounts");
        
        foreach ($uidCounts as $uidCount) {
            $uid = $uidCount->client_uid;
            $count = $uidCount->count;
            
            $this->line("   • {$uid} ({$count} accounts):");
            
            // Get all accounts for this UID
            $accounts = Client::where('client_uid', $uid)
                ->select('client_uid', 'client_status', 'volume_lots', 'volume_mln_usd', 'reward_usd', 'partner_account', 'client_id', 'reg_date')
                ->get();
            
            foreach ($accounts as $index => $account) {
                $this->line(sprintf(
                    '     %d. Status: %-10s | Lots: %-8s | USD: %-8s | Reward: %-8s | Partner: %s | Client ID: %s',
                    $index + 1,
                    $account->client_status,
                    number_format($account->volume_lots, 2),
                    number_format($account->volume_mln_usd, 2),
                    number_format($account->reward_usd, 2),
                    $account->partner_account ?? 'N/A',
                    $account->client_id ?? 'N/A'
                ));
            }
            $this->line('');
        }
        
        // Show summary statistics
        $totalDuplicateRecords = $uidCounts->sum('count');
        $totalUniqueUids = Client::distinct('client_uid')->count();
        $totalRecords = Client::count();
        
        $this->info('📊 Summary:');
        $this->line("   • Total Records: {$totalRecords}");
        $this->line("   • Unique UIDs: {$totalUniqueUids}");
        $this->line("   • Records with Duplicate UIDs: {$totalDuplicateRecords}");
        $this->line("   • Average accounts per UID: " . number_format($totalRecords / $totalUniqueUids, 2));
        $this->line('');
    }

    /**
     * Compare database data with API data
     */
    private function compareWithApi()
    {
        $this->info('🔄 Comparing with API data...');
        
        try {
            // Get API data
            $apiData = $this->clientService->getRawApiData();
            
            if (isset($apiData['error'])) {
                $this->error('❌ API Error: ' . $apiData['error']);
                return;
            }
            
            // Get V1 and V2 data from the correct fields
            $v1Api = $apiData['v1_api'] ?? [];
            $v2Api = $apiData['v2_api'] ?? [];
            
            $this->line("   • V1 API Clients: " . ($v1Api['total_clients'] ?? 0));
            $this->line("   • V2 API Clients: " . ($v2Api['total_clients'] ?? 0));
            
            // Show API data summary
            $this->info('📊 API Data Summary:');
            if (!empty($v1Api)) {
                $this->line("   • V1 API:");
                $this->line("     - Total Clients: " . ($v1Api['total_clients'] ?? 0));
                if (isset($v1Api['sample_client'])) {
                    $sample = $v1Api['sample_client'];
                    $this->line("     - Sample Volume (Lots): " . ($sample['volume_lots'] ?? 0));
                    $this->line("     - Sample Volume (USD): " . ($sample['volume_mln_usd'] ?? 0));
                    $this->line("     - Sample Reward (USD): " . ($sample['reward_usd'] ?? 0));
                }
            }
            
            if (!empty($v2Api)) {
                $this->line("   • V2 API:");
                $this->line("     - Total Clients: " . ($v2Api['total_clients'] ?? 0));
                if (isset($v2Api['sample_client'])) {
                    $sample = $v2Api['sample_client'];
                    $this->line("     - Sample Volume (Lots): " . ($sample['volume_lots'] ?? 0));
                    $this->line("     - Sample Volume (USD): " . ($sample['volume_mln_usd'] ?? 0));
                    $this->line("     - Sample Reward (USD): " . ($sample['reward_usd'] ?? 0));
                }
            }
            
            // Get database totals for comparison
            $dbTotalVolumeLots = Client::sum('volume_lots');
            $dbTotalVolumeUsd = Client::sum('volume_mln_usd');
            $dbTotalReward = Client::sum('reward_usd');
            
            $this->info('📊 Database Summary:');
            $this->line("   • Total Clients: " . Client::count());
            $this->line("   • Total Volume (Lots): " . number_format($dbTotalVolumeLots, 2));
            $this->line("   • Total Volume (USD): " . number_format($dbTotalVolumeUsd, 2));
            $this->line("   • Total Reward (USD): " . number_format($dbTotalReward, 2));
            
            // Show matching analysis if available
            if (isset($apiData['matching_analysis'])) {
                $analysis = $apiData['matching_analysis'];
                $this->info('📊 API Matching Analysis:');
                $this->line("   • Matching UIDs: " . ($analysis['matching_uids_count'] ?? 0));
                $this->line("   • V1 Only: " . ($analysis['v1_only_count'] ?? 0));
                $this->line("   • V2 Only: " . ($analysis['v2_only_count'] ?? 0));
            }
            
            // Show sample client UIDs if available
            if (!empty($v1Api['client_uids'])) {
                $this->info('📋 Sample V1 Client UIDs:');
                foreach ($v1Api['client_uids'] as $uid) {
                    $this->line("   • {$uid}");
                }
            }
            
            if (!empty($v2Api['client_uids'])) {
                $this->info('📋 Sample V2 Client UIDs:');
                foreach ($v2Api['client_uids'] as $uid) {
                    $this->line("   • {$uid}");
                }
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Error comparing with API: ' . $e->getMessage());
            Log::error('API comparison error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Show sample API data
     */
    private function showApiSampleData($clients, $limit = 3)
    {
        $this->line(str_repeat('-', 80));
        $this->line(sprintf('%-15s %-10s %-15s %-15s %-15s', 'Client UID', 'Status', 'Volume Lots', 'Volume USD', 'Reward USD'));
        $this->line(str_repeat('-', 80));
        
        $sampleClients = array_slice($clients, 0, $limit);
        
        foreach ($sampleClients as $client) {
            $this->line(sprintf(
                '%-15s %-10s %-15s %-15s %-15s',
                substr($client['client_uid'] ?? 'N/A', 0, 12) . '...',
                $client['client_status'] ?? 'N/A',
                number_format($client['volume_lots'] ?? 0, 2),
                number_format($client['volume_mln_usd'] ?? 0, 2),
                number_format($client['reward_usd'] ?? 0, 2)
            ));
        }
        
        $this->line(str_repeat('-', 80));
        $this->line('');
    }

    /**
     * Show API summary only
     */
    private function showApiSummary()
    {
        $this->info('📊 API Summary (Direct from API):');
        try {
            $apiData = $this->clientService->getRawApiData();
            if (isset($apiData['error'])) {
                $this->error('❌ API Error: ' . $apiData['error']);
                return;
            }
            $v1Clients = $apiData['v1_api']['all_clients'] ?? $apiData['v1_api']['clients'] ?? $apiData['v1_api']['data'] ?? [];
            $v2Clients = $apiData['v2_api']['all_clients'] ?? $apiData['v2_api']['clients'] ?? $apiData['v2_api']['data'] ?? [];
            $v1Count = count($v1Clients);
            $v2Count = count($v2Clients);
            $this->line("   • V1 API Clients: {$v1Count}");
            $this->line("   • V2 API Clients: {$v2Count}");
            // รวมข้อมูล V1
            $uids = [];
            $accounts = 0;
            $totalLots = 0;
            $totalUsd = 0;
            $totalProfit = 0;
            foreach ($v1Clients as $client) {
                if (isset($client['client_uid'])) {
                    $uids[$client['client_uid']] = true;
                }
                $accounts++;
                $totalLots += $client['volume_lots'] ?? 0;
                $totalUsd += $client['volume_mln_usd'] ?? 0;
                $totalProfit += $client['reward_usd'] ?? 0;
            }
            $this->line("   • V1 Summary: UID: ".count($uids).", Account: {$accounts}, Lots: ".number_format($totalLots, 4).", USD: ".number_format($totalUsd, 4).", Profit: ".number_format($totalProfit, 2));
            // รวมข้อมูล V2
            $uids2 = [];
            $accounts2 = 0;
            $totalLots2 = 0;
            $totalUsd2 = 0;
            $totalProfit2 = 0;
            foreach ($v2Clients as $client) {
                if (isset($client['client_uid'])) {
                    $uids2[$client['client_uid']] = true;
                }
                $accounts2++;
                $totalLots2 += $client['volume_lots'] ?? 0;
                $totalUsd2 += $client['volume_mln_usd'] ?? 0;
                $totalProfit2 += $client['reward_usd'] ?? 0;
            }
            $this->line("   • V2 Summary: UID: ".count($uids2).", Account: {$accounts2}, Lots: ".number_format($totalLots2, 4).", USD: ".number_format($totalUsd2, 4).", Profit: ".number_format($totalProfit2, 2));
        } catch (\Exception $e) {
            $this->error('❌ Error fetching API summary: ' . $e->getMessage());
        }
    }
} 