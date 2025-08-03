<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HamClient;
use App\Models\JanischaClient;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class CustomersController extends Controller
{
    public function index(Request $request)
    {
        try {
            Log::info('Fetching customers data for search...');

            // Get data from all client tables
            $hamClients = $this->getClientsData(HamClient::class, $request);
            $janischaClients = $this->getClientsData(JanischaClient::class, $request);

            // Combine all clients
            $allClients = $hamClients->concat($janischaClients);

            // Apply search filters
            $filters = [];
            $filteredClients = $allClients;

            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $filteredClients = $filteredClients->filter(function($client) use ($searchTerm) {
                    return stripos($client['client_uid'], $searchTerm) !== false ||
                           stripos($client['client_id'], $searchTerm) !== false ||
                           stripos($client['partner_account'], $searchTerm) !== false;
                });
                $filters['search'] = $searchTerm;
            }

            if ($request->filled('client_status')) {
                $status = $request->client_status;
                $filteredClients = $filteredClients->filter(function($client) use ($status) {
                    return $client['client_status'] === $status;
                });
                $filters['client_status'] = $status;
            }

            // Get all users for owner assignment
            $users = User::select('id', 'name', 'email', 'username')
                ->whereHas('roles', function($q) {
                    $q->whereIn('name', ['admin', 'super-admin', 'user']);
                })
                ->get();

            // Calculate statistics
            $stats = [
                'total_customers' => $filteredClients->count(),
                'total_volume_lots' => $filteredClients->sum('total_volume_lots'),
                'total_volume_usd' => $filteredClients->sum('total_volume_mln_usd'),
                'total_reward_usd' => $filteredClients->sum('total_reward_usd'),
                'total_rebate_usd' => 0,
                'active_customers' => $filteredClients->where('client_status', 'ACTIVE')->count(),
                'inactive_customers' => $filteredClients->where('client_status', 'INACTIVE')->count(),
            ];

            Log::info('Data formatted successfully', ['count' => $filteredClients->count()]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'customers' => $filteredClients->values(),
                        'stats' => $stats,
                        'users' => $users
                    ]
                ]);
            }

            return Inertia::render('Admin/Customers/Index', [
                'customers' => $filteredClients->values(),
                'stats' => $stats,
                'users' => $users,
                'filters' => $filters
            ]);

        } catch (\Exception $e) {
            Log::error('Error in CustomersController@index: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage()
                ], 500);
            }

            return Inertia::render('Admin/Customers/Index', [
                'customers' => collect([]),
                'stats' => [
                    'total_customers' => 0,
                    'total_volume_lots' => 0,
                    'total_volume_usd' => 0,
                    'total_reward_usd' => 0,
                    'total_rebate_usd' => 0,
                    'active_customers' => 0,
                    'inactive_customers' => 0,
                ],
                'users' => collect([]),
                'filters' => $filters ?? [],
                'error' => 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage()
            ]);
        }
    }

    private function getClientsData($modelClass, Request $request)
    {
        $query = $modelClass::query();

        // Apply search filters
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('client_uid', 'like', '%' . $searchTerm . '%')
                  ->orWhere('partner_account', 'like', '%' . $searchTerm . '%')
                  ->orWhere('client_id', 'like', '%' . $searchTerm . '%');
            });
        }

        if ($request->filled('client_status')) {
            $query->where('client_status', $request->client_status);
        }

        // Use pagination at database level for better performance
        $perPage = 50; // Increased from no pagination for better UX
        $clients = $query
            ->selectRaw('
                client_uid,
                MAX(partner_account) as partner_account,
                MAX(client_id) as client_id,
                MAX(reg_date) as reg_date,
                MAX(client_country) as client_country,
                SUM(volume_lots) as total_volume_lots,
                SUM(volume_mln_usd) as total_volume_mln_usd,
                SUM(reward_usd) as total_reward_usd,
                MAX(client_status) as client_status,
                MAX(kyc_passed) as kyc_passed,
                MAX(ftd_received) as ftd_received,
                MAX(ftt_made) as ftt_made,
                MAX(last_sync_at) as last_sync_at
            ')
            ->groupBy('client_uid')
            ->orderBy('reg_date', 'desc')
            ->paginate($perPage);

        // Format client data and add owner information
        $clients->getCollection()->transform(function ($client) {
            // Determine status from activity
            $volumeLots = (float)($client->total_volume_lots ?? 0);
            $rewardUsd = (float)($client->total_reward_usd ?? 0);
            $clientStatus = ($volumeLots > 0 || $rewardUsd > 0) ? 'ACTIVE' : 'INACTIVE';

            // KYC estimation based on activity level
            $kycPassed = ($volumeLots > 1.0 || $rewardUsd > 10.0) ? true : null;

            return [
                'client_uid' => $client->client_uid ?? '-',
                'partner_account' => $client->partner_account ?? '-',
                'client_id' => $client->client_id ?? '-',
                'reg_date' => $client->reg_date,
                'client_country' => $client->client_country ?? '-',
                'volume_lots' => $volumeLots,
                'volume_mln_usd' => (float)($client->total_volume_mln_usd ?? 0),
                'reward_usd' => $rewardUsd,
                'client_status' => $clientStatus,
                'kyc_passed' => $kycPassed,
                'ftd_received' => ($volumeLots > 0 || $rewardUsd > 0),
                'ftt_made' => ($volumeLots > 0),
                'last_sync_at' => $client->last_sync_at,
                'owner' => $this->getOwnerInfo($client->client_uid)
            ];
        });

        return $clients;
    }

    private function getOwnerInfo($clientUid)
    {
        $owners = [
            'hamsftmo@gmail.com' => 'Ham',
            'Janischa.trade@gmail.com' => 'Janischa'
        ];

        return $owners[$clientUid] ?? 'Unknown';
    }

    public function assignOwner(Request $request)
    {
        try {
            $request->validate([
                'client_uid' => 'required|string',
                'user_id' => 'required|exists:users,id'
            ]);

            // Update client records with user_id in all tables
            $updated = 0;

            $updated += HamClient::where('client_uid', $request->client_uid)->update(['user_id' => $request->user_id]);
            $updated += JanischaClient::where('client_uid', $request->client_uid)->update(['user_id' => $request->user_id]);

            if ($updated > 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'กำหนดเจ้าของลูกค้าเรียบร้อยแล้ว'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบข้อมูลลูกค้าที่ต้องการ'
                ], 404);
            }

        } catch (\Exception $e) {
            Log::error('Error assigning owner: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCustomerDetails(Request $request, $clientUid)
    {
        try {
            // Search in all tables
            $client = HamClient::where('client_uid', $clientUid)->first();
            $owner = 'Ham';

            if (!$client) {
                $client = JanischaClient::where('client_uid', $clientUid)->first();
                $owner = 'Janischa';
            }

            if (!$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบข้อมูลลูกค้า'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'client' => $client,
                    'owner' => $owner
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting customer details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStats(Request $request)
    {
        try {
            // Get data from all client tables
            $hamClients = $this->getClientsData(HamClient::class, $request);
            $janischaClients = $this->getClientsData(JanischaClient::class, $request);

            // Combine all clients
            $allClients = $hamClients->concat($janischaClients);

            // Calculate statistics
            $stats = [
                'total_customers' => $allClients->count(),
                'total_volume_lots' => $allClients->sum('total_volume_lots'),
                'total_volume_usd' => $allClients->sum('total_volume_mln_usd'),
                'total_reward_usd' => $allClients->sum('total_reward_usd'),
                'total_rebate_usd' => 0,
                'active_customers' => $allClients->where('client_status', 'ACTIVE')->count(),
                'inactive_customers' => $allClients->where('client_status', 'INACTIVE')->count(),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการดึงสถิติ: ' . $e->getMessage()
            ], 500);
        }
    }

    // Unified customer search from all sources
    public function allCustomers(Request $request)
    {
        Log::info('=== START: allCustomers method ===');
        $all = collect();
        $debugInfo = [];

        // 1. Local DB clients (fallback)
        $ham = HamClient::all();
        $janischa = JanischaClient::all();
        $clients = \App\Models\Client::all();
        
        // Also get data directly from ham_clients table
        $hamClientsTable = DB::table('ham_clients')->get();
        $janischaClientsTable = DB::table('janischa_clients')->get();
        
        // Add source and data_source to local DB data
        $ham = $ham->map(function($client) {
            $client->source = 'LocalDB_Ham';
            $client->data_source = 'Ham';
            return $client;
        });
        
        $janischa = $janischa->map(function($client) {
            $client->source = 'LocalDB_Janischa';
            $client->data_source = 'Janischa';
            return $client;
        });
        
        $clients = $clients->map(function($client) {
            $client->source = 'LocalDB_General';
            $client->data_source = 'Unknown';
            return $client;
        });
        
        $hamClientsTable = collect($hamClientsTable)->map(function($client) {
            $client->source = 'LocalDB_HamTable';
            $client->data_source = 'Ham';
            return $client;
        });
        
        $janischaClientsTable = collect($janischaClientsTable)->map(function($client) {
            $client->source = 'LocalDB_JanischaTable';
            $client->data_source = 'Janischa';
            return $client;
        });
        
        $all = $all->concat($ham)->concat($janischa)->concat($clients)
                   ->concat($hamClientsTable)->concat($janischaClientsTable);

        $debugInfo['local_db'] = $all->count();
        Log::info('Local DB clients count: ' . $all->count());

        // 2. XM (XMReportController@getTraderList) - Ham's data
        try {
            Log::info('=== Fetching XM data ===');
            $xm = app(\App\Http\Controllers\Admin\XMReportController::class)->getTraderList($request);
            
            $xmData = null;
            if (is_a($xm, 'Illuminate\Http\JsonResponse')) {
                $xmData = $xm->getData(true);
                Log::info('XM response type: JsonResponse');
            } elseif (method_exists($xm, 'getData')) {
                $xmData = $xm->getData();
                Log::info('XM response type: has getData method');
            } elseif (is_array($xm)) {
                $xmData = $xm;
                Log::info('XM response type: Array');
            } else {
                Log::info('XM response type: ' . gettype($xm));
            }
            
            if ($xmData && is_array($xmData)) {
                $xmClients = collect($xmData)->map(function($client) {
                    $client['source'] = 'XM';
                    $client['data_source'] = 'Ham';
                    return $client;
                });
                $all = $all->concat($xmClients);
                $debugInfo['xm'] = $xmClients->count();
                Log::info('XM (Ham) clients count: ' . $xmClients->count());
                
                // Debug: Log sample XM data
                if ($xmClients->count() > 0) {
                    Log::info('Sample XM client:', $xmClients->first());
                }
            } else {
                Log::warning('XM data is null or not array. Data: ' . json_encode($xmData));
                $debugInfo['xm'] = 0;
            }
        } catch (\Throwable $e) {
            Log::error('Error fetching XM data: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            $debugInfo['xm'] = 0;
        }

        // 3. client_account1 (Report1Controller@clientAccount1Api) - Ham's data
        try {
            Log::info('=== Fetching client_account1 data ===');
            
            // Call the new API endpoint that returns JSON
            $ca1 = app(\App\Http\Controllers\Admin\Report1Controller::class)->clientAccount1Api($request);
            
            $ca1Data = null;
            if (is_a($ca1, 'Illuminate\Http\JsonResponse')) {
                $ca1Data = $ca1->getData(true);
                Log::info('client_account1 response type: JsonResponse');
            } elseif (method_exists($ca1, 'getData')) {
                $ca1Data = $ca1->getData();
                Log::info('client_account1 response type: has getData method');
            } elseif (is_array($ca1)) {
                $ca1Data = $ca1;
                Log::info('client_account1 response type: Array');
            } else {
                Log::info('client_account1 response type: ' . gettype($ca1));
            }
            
            if ($ca1Data && isset($ca1Data['data']['clients'])) {
                $ca1Clients = collect($ca1Data['data']['clients'])->map(function($client) {
                    $client['source'] = 'Exness1_ClientAccount';
                    $client['data_source'] = 'Ham';
                    return $client;
                });
                $all = $all->concat($ca1Clients);
                $debugInfo['client_account1'] = $ca1Clients->count();
                Log::info('Exness1 Client Account (Ham) clients count: ' . $ca1Clients->count());
                
                // Debug: Log sample client_account1 data
                if ($ca1Clients->count() > 0) {
                    Log::info('Sample client_account1 client:', $ca1Clients->first());
                }
            } else {
                Log::warning('client_account1 data is null or missing clients array. Data: ' . json_encode($ca1Data));
                $debugInfo['client_account1'] = 0;
            }
        } catch (\Throwable $e) {
            Log::error('Error fetching client_account1 data: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            $debugInfo['client_account1'] = 0;
        }

        // 4. client_account (Report2Controller@clientAccount2Api) - Ham's data
        try {
            Log::info('=== Fetching client_account data ===');
            
            // Call the new API endpoint that returns JSON
            $ca2 = app(\App\Http\Controllers\Admin\Report2Controller::class)->clientAccount2Api($request);
            
            $ca2Data = null;
            if (is_a($ca2, 'Illuminate\Http\JsonResponse')) {
                $ca2Data = $ca2->getData(true);
                Log::info('client_account response type: JsonResponse');
            } elseif (method_exists($ca2, 'getData')) {
                $ca2Data = $ca2->getData();
                Log::info('client_account response type: has getData method');
            } elseif (is_array($ca2)) {
                $ca2Data = $ca2;
                Log::info('client_account response type: Array');
            } else {
                Log::info('client_account response type: ' . gettype($ca2));
            }
            
            if ($ca2Data && isset($ca2Data['data']['clients'])) {
                $ca2Clients = collect($ca2Data['data']['clients'])->map(function($client) {
                    $client['source'] = 'Exness2_ClientAccount';
                    $client['data_source'] = 'Janischa';
                    return $client;
                });
                $all = $all->concat($ca2Clients);
                $debugInfo['client_account'] = $ca2Clients->count();
                Log::info('Exness2 Client Account (Janischa) clients count: ' . $ca2Clients->count());
                
                // Debug: Log sample client_account data
                if ($ca2Clients->count() > 0) {
                    Log::info('Sample client_account client:', $ca2Clients->first());
                }
            } else {
                Log::warning('client_account data is null or missing clients array. Data: ' . json_encode($ca2Data));
                $debugInfo['client_account'] = 0;
            }
        } catch (\Throwable $e) {
            Log::error('Error fetching client_account data: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            $debugInfo['client_account'] = 0;
        }

        // 5. Exness/Janischa (ReportController@clients) - Exness1
        // REMOVED: exness1 and exness2 are no longer needed

        // 6. Exness/Ham (Report1Controller@clients1) - Exness2
        // REMOVED: exness1 and exness2 are no longer needed

        Log::info('Total clients before normalization: ' . $all->count());
        Log::info('Debug info by source:', $debugInfo);

        // Debug: Log sample data before normalization
        if ($all->count() > 0) {
            $sample = $all->first();
            Log::info('Sample client before normalization:', is_array($sample) ? $sample : $sample->toArray());
        }

        // Normalize fields for frontend search
        $normalized = $all->map(function ($c) {
            // Handle different data types safely
            if (is_array($c)) {
                $arr = $c;
            } elseif (is_object($c) && method_exists($c, 'toArray')) {
                $arr = $c->toArray();
            } elseif (is_object($c)) {
                $arr = (array) $c;
            } else {
                $arr = (array) $c;
            }
            
            $normalized = [
                'client_uid' => $arr['client_uid'] ?? $arr['clientId'] ?? $arr['traderId'] ?? $arr['account'] ?? $arr['login'] ?? $arr['id'] ?? null,
                'client_id' => $arr['client_id'] ?? $arr['clientId'] ?? $arr['traderId'] ?? $arr['account'] ?? $arr['login'] ?? $arr['id'] ?? null,
                'client_account' => $arr['client_account'] ?? $arr['account'] ?? $arr['login'] ?? $arr['traderId'] ?? $arr['clientId'] ?? null,
                'partner_account' => $arr['partner_account'] ?? $arr['campaign'] ?? $arr['partner'] ?? null,
                'traderId' => $arr['traderId'] ?? $arr['trader_id'] ?? $arr['clientId'] ?? $arr['account'] ?? null,
                'client_name' => $arr['client_name'] ?? $arr['name'] ?? $arr['full_name'] ?? $arr['first_name'] ?? null,
                'account_number' => $arr['account_number'] ?? $arr['account'] ?? $arr['login'] ?? $arr['account_id'] ?? null,
                'login' => $arr['login'] ?? $arr['account'] ?? $arr['account_id'] ?? null,
                'exness_id' => $arr['exness_id'] ?? $arr['exnessId'] ?? $arr['broker_id'] ?? null,
                'country' => $arr['client_country'] ?? $arr['country'] ?? $arr['nationality'] ?? null,
                'status' => $arr['client_status'] ?? $arr['status'] ?? $arr['valid'] ?? $arr['is_active'] ?? null,
                'reg_date' => $arr['reg_date'] ?? $arr['signUpDate'] ?? $arr['created_at'] ?? $arr['registration_date'] ?? null,
                'reward_usd' => $arr['reward_usd'] ?? $arr['total_reward_usd'] ?? $arr['commission'] ?? $arr['profit'] ?? 0,
                'rebate_amount_usd' => $arr['rebate_amount_usd'] ?? $arr['rebate'] ?? $arr['cashback'] ?? 0,
                'raw_data' => $arr['raw_data'] ?? null,
                'source' => $arr['source'] ?? null,
                'data_source' => $arr['data_source'] ?? null,
            ];
            
            return $normalized;
        })->values();

        Log::info('Total clients after normalization: ' . $normalized->count());
        Log::info('=== END: allCustomers method ===');

        return response()->json([
            'success' => true,
            'data' => [
                'customers' => $normalized,
                'debug_info' => $debugInfo
            ]
        ]);
    }

    public function syncData(Request $request)
    {
        try {
            Log::info('=== Starting Manual Sync ===');
            
            // Run sync commands
            $this->runSyncCommands();
            
            Log::info('=== Manual Sync Completed ===');
            
            return response()->json([
                'success' => true,
                'message' => 'Sync completed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Sync error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function syncHamData(Request $request)
    {
        try {
            Log::info('=== Starting Ham Data Sync ===');
            
            // Sync Ham-specific data
            $this->runHamSyncCommands();
            
            Log::info('=== Ham Data Sync Completed ===');
            
            return response()->json([
                'success' => true,
                'message' => 'Ham data sync completed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Ham sync error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ham sync failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function syncJanischaData(Request $request)
    {
        try {
            Log::info('=== Starting Janischa Data Sync ===');
            
            // Sync Janischa-specific data
            $this->runJanischaSyncCommands();
            
            Log::info('=== Janischa Data Sync Completed ===');
            
            return response()->json([
                'success' => true,
                'message' => 'Janischa data sync completed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Janischa sync error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Janischa sync failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function runSyncCommands()
    {
        // Run all sync commands
        $this->runHamSyncCommands();
        $this->runJanischaSyncCommands();
    }
    
    private function runHamSyncCommands()
    {
        // Sync Ham data from various sources
        Log::info('Running Ham sync commands...');
        
        // Sync XM data
        try {
            Artisan::call('xm:sync-traders');
            Log::info('XM traders sync completed');
        } catch (\Exception $e) {
            Log::error('XM traders sync failed: ' . $e->getMessage());
        }
        
        // Sync Exness1 data (Ham's data)
        try {
            Artisan::call('exness:sync-clients1');
            Log::info('Exness1 clients sync completed');
        } catch (\Exception $e) {
            Log::error('Exness1 clients sync failed: ' . $e->getMessage());
        }
    }
    
    private function runJanischaSyncCommands()
    {
        // Sync Janischa data from various sources
        Log::info('Running Janischa sync commands...');
        
        // Sync Exness2 data (Janischa's data)
        try {
            Artisan::call('exness:sync-clients2');
            Log::info('Exness2 clients sync completed');
        } catch (\Exception $e) {
            Log::error('Exness2 clients sync failed: ' . $e->getMessage());
        }
    }
}
