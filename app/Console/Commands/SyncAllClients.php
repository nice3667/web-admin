<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\HamExnessAuthService;
use App\Services\JanischaExnessAuthService;
use App\Models\HamClient;
use App\Models\JanischaClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SyncAllClients extends Command
{
    protected $signature = 'sync:all-clients {--force : Force sync even if data exists}';
    protected $description = 'Sync all clients data from Exness APIs to database';

    public function handle()
    {
        $this->info('Starting sync of all clients data...');
        
        // Sync Ham clients
        $this->info('Syncing Ham clients...');
        $this->syncHamClients();
        
        // Sync Janischa clients
        $this->info('Syncing Janischa clients...');
        $this->syncJanischaClients();
        
        $this->info('Sync completed!');
        
        // Show summary
        $this->showSummary();
    }
    
    private function syncHamClients()
    {
        try {
            $service = new HamExnessAuthService();
            $data = $service->getClientsData();
            
            if (isset($data['error'])) {
                $this->error('Ham API Error: ' . $data['error']);
                return;
            }
            
            $clients = $data['data'] ?? [];
            $this->info("Found " . count($clients) . " Ham clients from API");
            
            $synced = 0;
            $updated = 0;
            
            foreach ($clients as $clientData) {
                $clientUid = $clientData['client_uid'] ?? null;
                if (!$clientUid) continue;
                
                $existing = HamClient::where('client_uid', $clientUid)->first();
                
                $clientRecord = [
                    'partner_account' => $clientData['partner_account'] ?? '1172984151037556173',
                    'client_uid' => $clientUid,
                    'client_id' => $clientData['client_account'] ?? $clientData['client_id'] ?? null,
                    'reg_date' => $clientData['reg_date'] ?? now(),
                    'client_country' => $clientData['client_country'] ?? $clientData['country'] ?? null,
                    'volume_lots' => $clientData['volume_lots'] ?? 0,
                    'volume_mln_usd' => $clientData['volume_mln_usd'] ?? 0,
                    'reward_usd' => $clientData['reward_usd'] ?? 0,
                    'rebate_amount_usd' => $clientData['rebate_amount_usd'] ?? 0,
                    'client_status' => $clientData['client_status'] ?? 'UNKNOWN',
                    'kyc_passed' => $clientData['kyc_passed'] ?? false,
                    'ftd_received' => $clientData['ftd_received'] ?? false,
                    'ftt_made' => $clientData['ftt_made'] ?? false,
                    'raw_data' => $clientData,
                    'last_sync_at' => now(),
                ];
                
                if ($existing) {
                    $existing->update($clientRecord);
                    $updated++;
                } else {
                    HamClient::create($clientRecord);
                    $synced++;
                }
            }
            
            $this->info("Ham sync completed: {$synced} new, {$updated} updated");
            
        } catch (\Exception $e) {
            $this->error('Ham sync error: ' . $e->getMessage());
            Log::error('Ham sync error: ' . $e->getMessage());
        }
    }
    
    private function syncJanischaClients()
    {
        try {
            $service = new JanischaExnessAuthService();
            $data = $service->getClientsData();
            
            if (isset($data['error'])) {
                $this->error('Janischa API Error: ' . $data['error']);
                return;
            }
            
            $clients = $data['data'] ?? [];
            $this->info("Found " . count($clients) . " Janischa clients from API");
            
            $synced = 0;
            $updated = 0;
            
            foreach ($clients as $clientData) {
                $clientUid = $clientData['client_uid'] ?? null;
                if (!$clientUid) continue;
                
                $existing = JanischaClient::where('client_uid', $clientUid)->first();
                
                $clientRecord = [
                    'partner_account' => $clientData['partner_account'] ?? '1130924909696967913',
                    'client_uid' => $clientUid,
                    'client_id' => $clientData['client_account'] ?? $clientData['client_id'] ?? null,
                    'reg_date' => $clientData['reg_date'] ?? now(),
                    'client_country' => $clientData['client_country'] ?? $clientData['country'] ?? null,
                    'volume_lots' => $clientData['volume_lots'] ?? 0,
                    'volume_mln_usd' => $clientData['volume_mln_usd'] ?? 0,
                    'reward_usd' => $clientData['reward_usd'] ?? 0,
                    'rebate_amount_usd' => $clientData['rebate_amount_usd'] ?? 0,
                    'client_status' => $clientData['client_status'] ?? 'UNKNOWN',
                    'kyc_passed' => $clientData['kyc_passed'] ?? false,
                    'ftd_received' => $clientData['ftd_received'] ?? false,
                    'ftt_made' => $clientData['ftt_made'] ?? false,
                    'raw_data' => $clientData,
                    'last_sync_at' => now(),
                ];
                
                if ($existing) {
                    $existing->update($clientRecord);
                    $updated++;
                } else {
                    JanischaClient::create($clientRecord);
                    $synced++;
                }
            }
            
            $this->info("Janischa sync completed: {$synced} new, {$updated} updated");
            
        } catch (\Exception $e) {
            $this->error('Janischa sync error: ' . $e->getMessage());
            Log::error('Janischa sync error: ' . $e->getMessage());
        }
    }
    
    private function showSummary()
    {
        $hamCount = HamClient::count();
        $janischaCount = JanischaClient::count();
        
        $this->info("\n=== Database Summary ===");
        $this->info("Ham clients: {$hamCount}");
        $this->info("Janischa clients: {$janischaCount}");
        $this->info("Total clients: " . ($hamCount + $janischaCount));
    }
} 