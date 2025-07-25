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

            // Get data from external APIs
            $externalData = $this->getExternalData($request);

            // Combine all clients
            $allClients = $hamClients->concat($janischaClients)->concat($externalData);

            // Apply search filters
            $filters = [];
            $filteredClients = $allClients;

            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $filteredClients = $filteredClients->filter(function($client) use ($searchTerm) {
                    return stripos($client['client_uid'], $searchTerm) !== false ||
                           stripos($client['client_id'], $searchTerm) !== false ||
                           stripos($client['partner_account'], $searchTerm) !== false ||
                           stripos($client['client_account'], $searchTerm) !== false;
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
                'total_rebate_usd' => $filteredClients->sum('total_rebate_amount_usd'),
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
                  ->orWhere('client_id', 'like', '%' . $searchTerm . '%')
                  ->orWhere('raw_data->client_account', 'like', '%' . $searchTerm . '%');
            });
        }

        if ($request->filled('client_status')) {
            $query->where('client_status', $request->client_status);
        }

        // Get all data without pagination for better data completeness
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
                SUM(rebate_amount_usd) as total_rebate_amount_usd,
                MAX(client_status) as client_status,
                MAX(kyc_passed) as kyc_passed,
                MAX(ftd_received) as ftd_received,
                MAX(ftt_made) as ftt_made,
                MAX(last_sync_at) as last_sync_at,
                MAX(raw_data) as raw_data
            ')
            ->groupBy('client_uid')
            ->orderBy('reg_date', 'desc')
            ->get();

        // Format client data and add owner information
        $clients->transform(function ($client) {
            // Determine status from activity
            $volumeLots = (float)($client->total_volume_lots ?? 0);
            $rewardUsd = (float)($client->total_reward_usd ?? 0);
            $rebateUsd = (float)($client->total_rebate_amount_usd ?? 0);
            $clientStatus = ($volumeLots > 0 || $rewardUsd > 0) ? 'ACTIVE' : 'INACTIVE';

            // KYC estimation based on activity level
            $kycPassed = ($volumeLots > 1.0 || $rewardUsd > 10.0) ? true : null;

            // Get client_account from raw_data if available
            $rawData = is_string($client->raw_data) ? json_decode($client->raw_data, true) : $client->raw_data;
            $clientAccount = $rawData['client_account'] ?? $client->client_id ?? '-';

            return [
                'client_uid' => $client->client_uid ?? '-',
                'partner_account' => $client->partner_account ?? '-',
                'client_id' => $client->client_id ?? '-',
                'client_account' => $clientAccount,
                'reg_date' => $client->reg_date,
                'client_country' => $client->client_country ?? '-',
                'volume_lots' => $volumeLots,
                'volume_mln_usd' => (float)($client->total_volume_mln_usd ?? 0),
                'reward_usd' => $rewardUsd,
                'rebate_amount_usd' => $rebateUsd,
                'client_status' => $clientStatus,
                'kyc_passed' => $kycPassed,
                'ftd_received' => ($volumeLots > 0 || $rewardUsd > 0),
                'ftt_made' => ($volumeLots > 0),
                'last_sync_at' => $client->last_sync_at,
                'raw_data' => $rawData,
                'owner' => $this->getOwnerInfo($client)
            ];
        });

        return $clients;
    }

    private function getExternalData(Request $request)
    {
        $externalData = collect();

        // Try to get data from Report1Controller (Ham data)
        try {
            $report1Controller = app(\App\Http\Controllers\Admin\Report1Controller::class);
            $hamData = $report1Controller->clients1($request);
            
            if (method_exists($hamData, 'getData')) {
                $data = $hamData->getData();
                if (isset($data['data']['clients'])) {
                    $externalData = $externalData->concat($data['data']['clients']);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Could not fetch Ham data from Report1Controller: ' . $e->getMessage());
        }

        // Try to get data from ReportController (Janischa data)
        try {
            $reportController = app(\App\Http\Controllers\Admin\ReportController::class);
            $janischaData = $reportController->clients($request);
            
            if (method_exists($janischaData, 'getData')) {
                $data = $janischaData->getData();
                if (isset($data['data']['clients'])) {
                    $externalData = $externalData->concat($data['data']['clients']);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Could not fetch Janischa data from ReportController: ' . $e->getMessage());
        }

        // Try to get data from XMReportController (Ham data)
        try {
            $xmController = app(\App\Http\Controllers\Admin\XMReportController::class);
            $xmData = $xmController->getTraderList($request);
            
            if (method_exists($xmData, 'getData')) {
                $data = $xmData->getData();
                if (is_array($data)) {
                    // Transform XM data to match our format and mark as Ham
                    $transformedXmData = collect($data)->map(function ($trader) {
                        // Handle both array and object formats
                        $traderId = is_object($trader) ? $trader->traderId ?? $trader->trader_id ?? null : $trader['traderId'] ?? null;
                        $clientId = is_object($trader) ? $trader->clientId ?? $trader->client_id ?? null : $trader['clientId'] ?? null;
                        $country = is_object($trader) ? $trader->country ?? null : $trader['country'] ?? null;
                        $valid = is_object($trader) ? $trader->valid ?? false : $trader['valid'] ?? false;
                        $signUpDate = is_object($trader) ? $trader->signUpDate ?? null : $trader['signUpDate'] ?? null;
                        
                        return [
                            'client_uid' => $traderId,
                            'client_id' => $clientId ?? $traderId,
                            'client_account' => $traderId,
                            'partner_account' => 'XM_HAM', // Mark as Ham's XM data
                            'country' => $country,
                            'client_country' => $country,
                            'status' => $valid ? 'ACTIVE' : 'INACTIVE',
                            'client_status' => $valid ? 'ACTIVE' : 'INACTIVE',
                            'reg_date' => $signUpDate,
                            'reward_usd' => 0, // XM data doesn't have reward info
                            'rebate_amount_usd' => 0, // XM data doesn't have rebate info
                            'volume_lots' => 0,
                            'volume_mln_usd' => 0,
                            'raw_data' => $trader,
                            'owner' => 'Ham', // Explicitly mark as Ham
                            'source' => 'XM'
                        ];
                    });
                    $externalData = $externalData->concat($transformedXmData);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Could not fetch XM data: ' . $e->getMessage());
        }

        Log::info('External data fetched: ' . $externalData->count() . ' records');
        return $externalData;
    }

    private function getOwnerInfo($client)
    {
        // Check if this is a client object with partner_account
        if (is_object($client) && isset($client->partner_account)) {
            $partnerAccount = $client->partner_account;
        } elseif (is_array($client) && isset($client['partner_account'])) {
            $partnerAccount = $client['partner_account'];
        } else {
            return 'Unknown';
        }

        // Map partner_account to owner names
        $owners = [
            '1172984151037556173' => 'Ham',
            '1167686758601662826' => 'Janischa',
            '1130924909696967913' => 'Janischa',
            '1129255941915600142' => 'Ham',  // New partner account found
            'XM_HAM' => 'Ham',  // XM data belongs to Ham
            'low' => 'Ham',  // Additional partner account found
            'pay U' => 'Ham',  // Additional partner account found
            'RB you' => 'Ham',  // Additional partner account found
            'K.kan' => 'Ham'  // Additional partner account found
        ];

        return $owners[$partnerAccount] ?? 'Unknown';
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

    public function syncData(Request $request)
    {
        try {
            Log::info('Starting manual sync of clients data...');
            
            // Run the sync command
            Artisan::call('sync:all-clients');
            
            $output = Artisan::output();
            Log::info('Sync command output: ' . $output);
            
            return response()->json([
                'success' => true,
                'message' => 'Sync completed successfully',
                'output' => $output
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in manual sync: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการ sync: ' . $e->getMessage()
            ], 500);
        }
    }

    // Unified customer search from all sources
    public function allCustomers(Request $request)
    {
        $all = collect();

        // 1. Local DB clients
        $ham = HamClient::all();
        $janischa = JanischaClient::all();
        $clients = \App\Models\Client::all(); // Add Client model data
        
        // Also get data directly from ham_clients table
        $hamClientsTable = DB::table('ham_clients')->get();
        $janischaClientsTable = DB::table('janischa_clients')->get();
        
        $all = $all->concat($ham)->concat($janischa)->concat($clients)
                   ->concat($hamClientsTable)->concat($janischaClientsTable);

        Log::info('Local DB clients count: ' . $all->count());
        Log::info('Client model count: ' . $clients->count());
        Log::info('Ham clients table count: ' . $hamClientsTable->count());
        Log::info('Janischa clients table count: ' . $janischaClientsTable->count());

        // 2. Exness/Janischa (ReportController)
        try {
            $exness = app(\App\Http\Controllers\Admin\ReportController::class)->clients($request);
            if (method_exists($exness, 'getData')) {
                $exnessData = $exness->getData();
                if (isset($exnessData['data']['clients'])) {
                    $all = $all->concat($exnessData['data']['clients']);
                }
            } elseif (is_a($exness, 'Illuminate\Http\JsonResponse')) {
                $data = $exness->getData(true);
                if (isset($data['data']['clients'])) {
                    $all = $all->concat($data['data']['clients']);
                }
            } elseif (is_object($exness) && method_exists($exness, 'toArray')) {
                $exnessData = $exness->toArray();
                if (isset($exnessData['data']['clients'])) {
                    $all = $all->concat($exnessData['data']['clients']);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Error fetching Exness/Janischa data: ' . $e->getMessage());
            
            // Fallback: Get data directly from JanischaClient model
            try {
                $janischaClients = \App\Models\JanischaClient::all();
                Log::info('Fallback: Using JanischaClient model data, count: ' . $janischaClients->count());
                $all = $all->concat($janischaClients);
            } catch (\Throwable $fallbackError) {
                Log::error('Error in JanischaClient fallback: ' . $fallbackError->getMessage());
            }
        }

        // 3. Exness/Ham (Report1Controller)
        try {
            $exness1 = app(\App\Http\Controllers\Admin\Report1Controller::class)->clients1($request);
            if (method_exists($exness1, 'getData')) {
                $exnessData1 = $exness1->getData();
                if (isset($exnessData1['data']['clients'])) {
                    $all = $all->concat($exnessData1['data']['clients']);
                }
            } elseif (is_a($exness1, 'Illuminate\Http\JsonResponse')) {
                $data = $exness1->getData(true);
                if (isset($data['data']['clients'])) {
                    $all = $all->concat($data['data']['clients']);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Error fetching Exness/Ham data: ' . $e->getMessage());
        }



        // 5. XM (XMReportController)
        try {
            $xm = app(\App\Http\Controllers\Admin\XMReportController::class)->getTraderList($request);
            if (method_exists($xm, 'getData')) {
                $xmData = $xm->getData();
                if (is_array($xmData)) {
                    $all = $all->concat($xmData);
                }
            } elseif (is_a($xm, 'Illuminate\Http\JsonResponse')) {
                $data = $xm->getData(true);
                if (is_array($data)) {
                    $all = $all->concat($data);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Error fetching XM data: ' . $e->getMessage());
        }

        Log::info('Total clients before normalization: ' . $all->count());

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
            
            // Debug: Log the array structure (only for first few items to avoid spam)
            static $logCount = 0;
            if ($logCount < 3) {
                Log::info('Normalizing client ' . $logCount . ':', $arr);
                $logCount++;
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
                            'raw_data' => $arr['raw_data'] ?? null, // Add raw_data for search
                        ];
            
            return $normalized;
        })->values();

        Log::info('Total clients after normalization: ' . $normalized->count());

        return response()->json([
            'success' => true,
            'data' => [
                'customers' => $normalized,
            ]
        ]);
    }
}
