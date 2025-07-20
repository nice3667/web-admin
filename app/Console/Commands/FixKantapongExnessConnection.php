<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\KantapongExnessAuthService;
use App\Models\KantapongClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class FixKantapongExnessConnection extends Command
{
    protected $signature = 'fix:kantapong-exness {--test : Only test connection without syncing}';
    protected $description = 'Fix and test Kantapong Exness API connection and sync data';

    public function handle()
    {
        $this->info('🔧 Kantapong Exness Connection Fixer');
        $this->info('====================================');

        $kantapongService = new KantapongExnessAuthService();

        // Step 1: Test API Connection
        $this->info('1️⃣ Testing API Connection...');
        $connectionTest = $this->testConnection($kantapongService);
        
        if (!$connectionTest['success']) {
            $this->error('❌ Connection failed: ' . $connectionTest['message']);
            return 1;
        }

        $this->info('✅ Connection successful!');
        $this->info('Token preview: ' . $connectionTest['token']);

        // Step 2: Test Data Retrieval
        $this->info('');
        $this->info('2️⃣ Testing Data Retrieval...');
        $dataTest = $this->testDataRetrieval($kantapongService);
        
        if (!$dataTest['success']) {
            $this->error('❌ Data retrieval failed: ' . $dataTest['message']);
            return 1;
        }

        $this->info('✅ Data retrieval successful!');
        $this->info('Found ' . $dataTest['count'] . ' clients');

        // Step 3: Check Database Status
        $this->info('');
        $this->info('3️⃣ Checking Database Status...');
        $dbStatus = $this->checkDatabaseStatus();
        $this->info('Database records: ' . $dbStatus['count']);
        $this->info('Last sync: ' . ($dbStatus['last_sync'] ?? 'Never'));

        if ($this->option('test')) {
            $this->info('');
            $this->info('🧪 Test completed successfully!');
            return 0;
        }

        // Step 4: Force Sync Data
        $this->info('');
        $this->info('4️⃣ Syncing Fresh Data...');
        
        // Clear cache first
        Cache::forget('kantapong_exness_token');
        Cache::forget('kantapong_exness_clients_data');
        $this->info('Cache cleared');

        $syncResult = $this->forceSyncData($kantapongService);
        
        if (!$syncResult['success']) {
            $this->error('❌ Sync failed: ' . $syncResult['message']);
            return 1;
        }

        $this->info('✅ Sync completed successfully!');
        $this->info('Created: ' . $syncResult['created'] . ' records');
        $this->info('Updated: ' . $syncResult['updated'] . ' records');

        // Step 5: Verify Fix
        $this->info('');
        $this->info('5️⃣ Verifying Fix...');
        $verification = $this->verifyFix();
        
        if ($verification['success']) {
            $this->info('✅ Fix verified! Kantapong data is now available.');
            $this->info('Total records: ' . $verification['total']);
            $this->info('Active clients: ' . $verification['active']);
        } else {
            $this->error('❌ Verification failed: ' . $verification['message']);
        }

        return 0;
    }

    private function testConnection($kantapongService): array
    {
        try {
            $token = $kantapongService->retrieveToken();
            
            if (!$token) {
                return [
                    'success' => false,
                    'message' => 'ไม่สามารถรับ JWT Token ได้',
                    'token' => null
                ];
            }

            return [
                'success' => true,
                'message' => 'เชื่อมต่อสำเร็จ',
                'token' => substr($token, 0, 20) . '...'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
                'token' => null
            ];
        }
    }

    private function testDataRetrieval($kantapongService): array
    {
        try {
            $this->info('Testing V1 API...');
            $v1Data = $kantapongService->getClientsFromUrl(
                'https://my.exnessaffiliates.com/api/reports/clients/',
                'v1'
            );

            $this->info('Testing V2 API...');
            $v2Data = $kantapongService->getClientsFromUrl(
                'https://my.exnessaffiliates.com/api/v2/reports/clients/',
                'v2'
            );

            $v1Count = isset($v1Data['data']) ? count($v1Data['data']) : 0;
            $v2Count = isset($v2Data['data']) ? count($v2Data['data']) : 0;

            $this->info("V1 API: {$v1Count} records");
            $this->info("V2 API: {$v2Count} records");

            if ($v1Count === 0 && $v2Count === 0) {
                return [
                    'success' => false,
                    'message' => 'No data found in both APIs',
                    'count' => 0
                ];
            }

            return [
                'success' => true,
                'message' => 'Data retrieved successfully',
                'count' => max($v1Count, $v2Count),
                'v1_count' => $v1Count,
                'v2_count' => $v2Count
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'count' => 0
            ];
        }
    }

    private function checkDatabaseStatus(): array
    {
        $count = KantapongClient::count();
        $lastSync = KantapongClient::max('last_sync_at');
        
        return [
            'count' => $count,
            'last_sync' => $lastSync ? Carbon::parse($lastSync)->diffForHumans() : null
        ];
    }

    private function forceSyncData($kantapongService): array
    {
        try {
            // Get fresh data from API
            $apiResponse = $kantapongService->getClientsData();

            if (isset($apiResponse['error'])) {
                return [
                    'success' => false,
                    'message' => $apiResponse['error']
                ];
            }

            if (!isset($apiResponse['data']) || empty($apiResponse['data'])) {
                return [
                    'success' => false,
                    'message' => 'No data received from API'
                ];
            }

            $clients = $apiResponse['data'];
            $created = 0;
            $updated = 0;

            foreach ($clients as $clientData) {
                try {
                    $clientUid = $clientData['client_uid'] ?? null;
                    if (!$clientUid) {
                        continue;
                    }

                    // Prepare data for database
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

                    // Update or create client
                    $client = KantapongClient::updateOrCreate(
                        ['client_uid' => $clientUid],
                        $dbData
                    );

                    if ($client->wasRecentlyCreated) {
                        $created++;
                    } else {
                        $updated++;
                    }

                } catch (\Exception $e) {
                    Log::error('Error processing Kantapong client during fix', [
                        'client_uid' => $clientData['client_uid'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }

            return [
                'success' => true,
                'message' => 'Sync completed',
                'created' => $created,
                'updated' => $updated,
                'total' => count($clients)
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function verifyFix(): array
    {
        try {
            $total = KantapongClient::count();
            $active = KantapongClient::where('client_status', 'ACTIVE')->count();
            $recentSync = KantapongClient::where('last_sync_at', '>', now()->subMinutes(10))->count();

            if ($total === 0) {
                return [
                    'success' => false,
                    'message' => 'No records found after sync'
                ];
            }

            if ($recentSync === 0) {
                return [
                    'success' => false,
                    'message' => 'No recently synced records found'
                ];
            }

            return [
                'success' => true,
                'total' => $total,
                'active' => $active,
                'recent_sync' => $recentSync
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
} 