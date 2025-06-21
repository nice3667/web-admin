<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\HamExnessAuthService;
use App\Models\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class SyncReport1Data extends Command
{
    protected $signature = 'sync:report1-data {--daemon : Run in daemon mode} {--interval=30 : Sync interval in minutes} {--new-only : Sync only new clients}';
    protected $description = 'Sync Report1 data from Exness API (Ham account)';

    protected $hamExnessAuthService;

    public function __construct(HamExnessAuthService $hamExnessAuthService)
    {
        parent::__construct();
        $this->hamExnessAuthService = $hamExnessAuthService;
    }

    public function handle()
    {
        $isDaemon = $this->option('daemon');
        $interval = (int) $this->option('interval');
        $newOnly = $this->option('new-only');

        $this->info("Starting Report1 Data Sync (Ham account)");
        $this->info("Mode: " . ($isDaemon ? "Daemon" : "Single run"));
        $this->info("Interval: {$interval} minutes");
        $this->info("New only: " . ($newOnly ? "Yes" : "No"));

        do {
            $this->syncData($newOnly);
            
            if ($isDaemon) {
                $this->info("Waiting {$interval} minutes before next sync...");
                sleep($interval * 60);
            }
        } while ($isDaemon);

        return 0;
    }

    private function syncData($newOnly = false)
    {
        try {
            $this->info("Fetching data from Exness API (Ham)...");
            
            $result = $this->hamExnessAuthService->getClientsData();
            
            if (isset($result['error'])) {
                $this->error("API Error: " . $result['error']);
                return;
            }

            $clients = $result['data'];
            $this->info("Fetched " . count($clients) . " clients from API");

            $newCount = 0;
            $updatedCount = 0;

            // สร้างตาราง ham_clients ถ้ายังไม่มี
            $this->createHamClientsTable();

            foreach ($clients as $clientData) {
                if (!isset($clientData['client_uid'])) {
                    continue;
                }

                $clientUid = $clientData['client_uid'];
                
                // Check if client exists in ham_clients table
                $existingClient = DB::table('ham_clients')->where('client_uid', $clientUid)->first();
                
                if ($existingClient) {
                    if (!$newOnly) {
                        // Update existing client
                        DB::table('ham_clients')
                            ->where('client_uid', $clientUid)
                            ->update([
                                'partner_account' => $clientData['partner_account'] ?? $existingClient->partner_account,
                                'client_country' => $clientData['client_country'] ?? $clientData['country'] ?? $existingClient->client_country,
                                'reg_date' => isset($clientData['reg_date']) ? Carbon::parse($clientData['reg_date']) : $existingClient->reg_date,
                                'volume_lots' => $clientData['volume_lots'] ?? $existingClient->volume_lots,
                                'volume_mln_usd' => $clientData['volume_mln_usd'] ?? $existingClient->volume_mln_usd,
                                'reward_usd' => $clientData['reward_usd'] ?? $existingClient->reward_usd,
                                'rebate_amount_usd' => $clientData['rebate_amount_usd'] ?? $existingClient->rebate_amount_usd,
                                'kyc_passed' => isset($clientData['kyc_passed']) ? (bool)$clientData['kyc_passed'] : $existingClient->kyc_passed,
                                'ftd_received' => isset($clientData['ftd_received']) ? (bool)$clientData['ftd_received'] : $existingClient->ftd_received,
                                'ftt_made' => isset($clientData['ftt_made']) ? (bool)$clientData['ftt_made'] : $existingClient->ftt_made,
                                'updated_at' => now(),
                            ]);
                        $updatedCount++;
                    }
                } else {
                    // Create new client
                    DB::table('ham_clients')->insert([
                        'client_uid' => $clientUid,
                        'partner_account' => $clientData['partner_account'] ?? null,
                        'client_country' => $clientData['client_country'] ?? $clientData['country'] ?? null,
                        'reg_date' => isset($clientData['reg_date']) ? Carbon::parse($clientData['reg_date']) : null,
                        'volume_lots' => $clientData['volume_lots'] ?? 0,
                        'volume_mln_usd' => $clientData['volume_mln_usd'] ?? 0,
                        'reward_usd' => $clientData['reward_usd'] ?? 0,
                        'rebate_amount_usd' => $clientData['rebate_amount_usd'] ?? 0,
                        'kyc_passed' => isset($clientData['kyc_passed']) ? (bool)$clientData['kyc_passed'] : false,
                        'ftd_received' => isset($clientData['ftd_received']) ? (bool)$clientData['ftd_received'] : false,
                        'ftt_made' => isset($clientData['ftt_made']) ? (bool)$clientData['ftt_made'] : false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $newCount++;
                }
            }

            $this->info("Sync completed successfully!");
            $this->info("New clients: {$newCount}");
            $this->info("Updated clients: {$updatedCount}");

            Log::info('Report1 Data Sync completed', [
                'account' => 'Ham',
                'total_fetched' => count($clients),
                'new_clients' => $newCount,
                'updated_clients' => $updatedCount,
                'new_only' => $newOnly
            ]);

        } catch (\Exception $e) {
            $this->error("Sync failed: " . $e->getMessage());
            Log::error('Report1 Data Sync failed', [
                'account' => 'Ham',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function createHamClientsTable()
    {
        if (!Schema::hasTable('ham_clients')) {
            Schema::create('ham_clients', function ($table) {
                $table->id();
                $table->string('client_uid')->unique();
                $table->string('partner_account')->nullable();
                $table->string('client_country')->nullable();
                $table->timestamp('reg_date')->nullable();
                $table->decimal('volume_lots', 15, 4)->default(0);
                $table->decimal('volume_mln_usd', 15, 4)->default(0);
                $table->decimal('reward_usd', 15, 4)->default(0);
                $table->decimal('rebate_amount_usd', 15, 4)->default(0);
                $table->boolean('kyc_passed')->default(false);
                $table->boolean('ftd_received')->default(false);
                $table->boolean('ftt_made')->default(false);
                $table->timestamps();
                
                $table->index('client_uid');
                $table->index('partner_account');
                $table->index('reg_date');
            });
            $this->info("Created ham_clients table");
        }
    }
} 