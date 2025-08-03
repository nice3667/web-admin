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
use Illuminate\Support\Facades\Cache;

class CustomersController extends Controller
{
    public function index(Request $request)
    {
        try {
            Log::info('Fetching customers data for search...');

            // Get data from both Janischa and Ham clients
            $allClients = $this->getClientsData($request);

            // Apply search filters
            $filters = [];
            $filteredClients = $allClients;

            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $filteredClients = $filteredClients->filter(function($client) use ($searchTerm) {
                    // ค้นหาในทุกฟิลด์ที่เกี่ยวข้อง
                    $searchFields = [
                        $client['client_uid'] ?? '',
                        $client['client_id'] ?? '',
                        $client['client_account'] ?? '',
                        $client['partner_account'] ?? '',
                        $client['raw_data']['client_account'] ?? '',
                        $client['raw_data']['client_id'] ?? '',
                        $client['raw_data']['partner_account'] ?? '',
                        $client['raw_data']['client_name'] ?? '',
                        $client['raw_data']['account'] ?? '',
                        $client['raw_data']['login'] ?? '',
                        $client['raw_data']['traderId'] ?? '',
                        $client['raw_data']['id'] ?? ''
                    ];
                    
                    foreach ($searchFields as $field) {
                        if (stripos($field, $searchTerm) !== false) {
                            return true;
                        }
                    }
                    return false;
                });
                $filters['search'] = $searchTerm;
                
                // ถ้าไม่พบข้อมูลและมีการค้นหา ให้ลอง sync ข้อมูลใหม่
                if ($filteredClients->count() === 0) {
                    Log::info('No results found for search term: ' . $searchTerm . ', attempting to sync fresh data...');
                    
                    // Run sync commands in background
                    try {
                        Artisan::call('exness:sync-clients1'); // Sync Ham data
                        Artisan::call('exness:sync-clients2'); // Sync Janischa data
                        Log::info('Background sync completed');
                        
                        // Get fresh data after sync
                        $allClients = $this->getClientsData($request);
                        $filteredClients = $allClients->filter(function($client) use ($searchTerm) {
                            $searchFields = [
                                $client['client_uid'] ?? '',
                                $client['client_id'] ?? '',
                                $client['client_account'] ?? '',
                                $client['partner_account'] ?? '',
                                $client['raw_data']['client_account'] ?? '',
                                $client['raw_data']['client_id'] ?? '',
                                $client['raw_data']['partner_account'] ?? '',
                                $client['raw_data']['client_name'] ?? '',
                                $client['raw_data']['account'] ?? '',
                                $client['raw_data']['login'] ?? '',
                                $client['raw_data']['traderId'] ?? '',
                                $client['raw_data']['id'] ?? ''
                            ];
                            
                            foreach ($searchFields as $field) {
                                if (stripos($field, $searchTerm) !== false) {
                                    return true;
                                }
                            }
                            return false;
                        });
                    } catch (\Exception $e) {
                        Log::error('Background sync failed: ' . $e->getMessage());
                    }
                }
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
                'total_volume_lots' => $filteredClients->sum('volume_lots'),
                'total_volume_usd' => $filteredClients->sum('volume_mln_usd'),
                'total_reward_usd' => $filteredClients->sum('reward_usd'),
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

            // เพิ่มข้อความแจ้งเตือนเมื่อไม่พบข้อมูล
            $searchMessage = null;
            if ($request->filled('search') && $filteredClients->count() === 0) {
                $searchMessage = 'ไม่พบลูกค้าที่ตรงกับเงื่อนไขการค้นหา "' . $request->search . '" (แสดงเฉพาะผลลัพธ์ที่กรอง) - ลอง sync ข้อมูลใหม่หรือตรวจสอบคำค้นหา';
            }

            return Inertia::render('Admin/Customers/Index', [
                'customers' => $filteredClients->values(),
                'stats' => $stats,
                'users' => $users,
                'filters' => $filters,
                'searchMessage' => $searchMessage
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

    private function getClientsData(Request $request)
    {
        // ดึงข้อมูลจากทั้ง Janischa และ Ham clients
        $janischaQuery = JanischaClient::query();
        $hamQuery = HamClient::query();

        // Apply search filters to both queries
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $janischaQuery->where(function($q) use ($searchTerm) {
                $q->where('client_uid', 'like', '%' . $searchTerm . '%')
                  ->orWhere('partner_account', 'like', '%' . $searchTerm . '%')
                  ->orWhere('client_id', 'like', '%' . $searchTerm . '%')
                  ->orWhereRaw('JSON_EXTRACT(raw_data, "$.client_account") LIKE ?', ['%' . $searchTerm . '%'])
                  ->orWhereRaw('JSON_EXTRACT(raw_data, "$.client_id") LIKE ?', ['%' . $searchTerm . '%'])
                  ->orWhereRaw('JSON_EXTRACT(raw_data, "$.partner_account") LIKE ?', ['%' . $searchTerm . '%'])
                  ->orWhereRaw('JSON_EXTRACT(raw_data, "$.client_name") LIKE ?', ['%' . $searchTerm . '%'])
                  ->orWhereRaw('JSON_EXTRACT(raw_data, "$.account") LIKE ?', ['%' . $searchTerm . '%'])
                  ->orWhereRaw('JSON_EXTRACT(raw_data, "$.login") LIKE ?', ['%' . $searchTerm . '%'])
                  ->orWhereRaw('JSON_EXTRACT(raw_data, "$.traderId") LIKE ?', ['%' . $searchTerm . '%'])
                  ->orWhereRaw('JSON_EXTRACT(raw_data, "$.id") LIKE ?', ['%' . $searchTerm . '%']);
            });
            $hamQuery->where(function($q) use ($searchTerm) {
                $q->where('client_uid', 'like', '%' . $searchTerm . '%')
                  ->orWhere('partner_account', 'like', '%' . $searchTerm . '%')
                  ->orWhere('client_id', 'like', '%' . $searchTerm . '%')
                  ->orWhereRaw('JSON_EXTRACT(raw_data, "$.client_account") LIKE ?', ['%' . $searchTerm . '%'])
                  ->orWhereRaw('JSON_EXTRACT(raw_data, "$.client_id") LIKE ?', ['%' . $searchTerm . '%'])
                  ->orWhereRaw('JSON_EXTRACT(raw_data, "$.partner_account") LIKE ?', ['%' . $searchTerm . '%'])
                  ->orWhereRaw('JSON_EXTRACT(raw_data, "$.client_name") LIKE ?', ['%' . $searchTerm . '%'])
                  ->orWhereRaw('JSON_EXTRACT(raw_data, "$.account") LIKE ?', ['%' . $searchTerm . '%'])
                  ->orWhereRaw('JSON_EXTRACT(raw_data, "$.login") LIKE ?', ['%' . $searchTerm . '%'])
                  ->orWhereRaw('JSON_EXTRACT(raw_data, "$.traderId") LIKE ?', ['%' . $searchTerm . '%'])
                  ->orWhereRaw('JSON_EXTRACT(raw_data, "$.id") LIKE ?', ['%' . $searchTerm . '%']);
            });
        }

        if ($request->filled('client_status')) {
            $janischaQuery->where('client_status', $request->client_status);
            $hamQuery->where('client_status', $request->client_status);
        }

        // ดึงข้อมูลจากทั้งสองตาราง
        $janischaClients = $janischaQuery->get();
        $hamClients = $hamQuery->get();

        // รวมข้อมูลจากทั้งสองตารางและจัดการข้อมูลที่ซ้ำกัน
        $allClients = $janischaClients->concat($hamClients);
        
        // Group by client_uid และรวมข้อมูล
        $groupedClients = $allClients->groupBy('client_uid')->map(function ($clients) {
            // หา record ที่มี raw_data ที่ดีที่สุด (มี client_account)
            $bestClient = $clients->first();
            foreach ($clients as $client) {
                if ($client->raw_data && is_array($client->raw_data) && 
                    isset($client->raw_data['client_account']) && 
                    !empty($client->raw_data['client_account'])) {
                    $bestClient = $client;
                    break;
                }
            }
            
            // รวมข้อมูลตัวเลข
            $totalVolumeLots = $clients->sum('volume_lots');
            $totalVolumeMlnUsd = $clients->sum('volume_mln_usd');
            $totalRewardUsd = $clients->sum('reward_usd');
            
            // ใช้ข้อมูลจาก record ที่ดีที่สุดสำหรับข้อมูลที่ไม่ใช่ตัวเลข
            $client = (object) [
                'client_uid' => $bestClient->client_uid,
                'partner_account' => $bestClient->partner_account,
                'client_id' => $bestClient->client_id,
                'reg_date' => $bestClient->reg_date,
                'client_country' => $bestClient->client_country,
                'total_volume_lots' => $totalVolumeLots,
                'total_volume_mln_usd' => $totalVolumeMlnUsd,
                'total_reward_usd' => $totalRewardUsd,
                'client_status' => $bestClient->client_status,
                'kyc_passed' => $bestClient->kyc_passed,
                'ftd_received' => $bestClient->ftd_received,
                'ftt_made' => $bestClient->ftt_made,
                'last_sync_at' => $bestClient->last_sync_at,
                'raw_data' => $bestClient->raw_data
            ];
            
            return $client;
        })->values();

        // Format client data and add owner information
        $groupedClients->transform(function ($client) {
            // Determine status from activity
            $volumeLots = (float)($client->total_volume_lots ?? 0);
            $rewardUsd = (float)($client->total_reward_usd ?? 0);
            $clientStatus = ($volumeLots > 0 || $rewardUsd > 0) ? 'ACTIVE' : 'INACTIVE';

            // KYC estimation based on activity level
            $kycPassed = ($volumeLots > 1.0 || $rewardUsd > 10.0) ? true : null;

            // ดึง client_account จาก raw_data สำหรับ Janischa
            $clientAccount = $this->getClientAccount($client);

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
                'client_status' => $clientStatus,
                'kyc_passed' => $kycPassed,
                'ftd_received' => ($volumeLots > 0 || $rewardUsd > 0),
                'ftt_made' => ($volumeLots > 0),
                'last_sync_at' => $client->last_sync_at,
                'owner' => $this->getOwnerInfo($client->client_uid)
            ];
        });

        return $groupedClients;
    }

    private function getClientAccount($client)
    {
        // Debug: Log raw_data structure for troubleshooting
        if ($client->raw_data) {
            Log::info('Client raw_data:', [
                'client_uid' => $client->client_uid,
                'raw_data' => $client->raw_data,
                'has_client_account' => isset($client->raw_data['client_account']),
                'has_client_name' => isset($client->raw_data['client_name']),
                'client_id' => $client->client_id,
            ]);
        }

        // Try to get client_account from raw_data first (สำหรับ Janischa)
        if ($client->raw_data && is_array($client->raw_data)) {
            // ตรวจสอบ client_account ใน raw_data (สำหรับ Janischa)
            if (isset($client->raw_data['client_account']) && !empty($client->raw_data['client_account'])) {
                return (string)$client->raw_data['client_account'];
            }
            
            // ตรวจสอบ client_name ใน raw_data
            if (isset($client->raw_data['client_name']) && !empty($client->raw_data['client_name'])) {
                return (string)$client->raw_data['client_name'];
            }
            
            // ตรวจสอบฟิลด์อื่นๆ ที่อาจเป็น account number
            $possibleFields = ['account', 'login', 'account_number', 'account_id', 'traderId', 'id'];
            foreach ($possibleFields as $field) {
                if (isset($client->raw_data[$field]) && !empty($client->raw_data[$field])) {
                    return (string)$client->raw_data[$field];
                }
            }
        }
        
        // Fallback to database fields - use client_id as client_account
        if (!empty($client->client_id)) {
            // ตรวจสอบว่า client_id เป็น account number จริงหรือไม่ (เป็นตัวเลขและไม่ใช่รูปแบบ JAN001)
            if (is_numeric($client->client_id) && !preg_match('/^[A-Z]+/', $client->client_id)) {
                return (string)$client->client_id;
            }
        }
        
        // ใช้ client_uid เป็น fallback สุดท้าย
        if (!empty($client->client_uid)) {
            // ตรวจสอบว่า client_uid เป็น account number จริงหรือไม่
            if (is_numeric($client->client_uid) && !preg_match('/^[A-Z]+/', $client->client_uid)) {
                return (string)$client->client_uid;
            }
        }
        
        return '-';
    }

    private function getOwnerInfo($clientUid)
    {
        // ตรวจสอบว่า client_uid อยู่ในตารางไหน
        $janischaClient = JanischaClient::where('client_uid', $clientUid)->first();
        if ($janischaClient) {
            return 'Janischa';
        }
        
        $hamClient = HamClient::where('client_uid', $clientUid)->first();
        if ($hamClient) {
            return 'Ham';
        }
        
        return 'Unknown';
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
            $hamClients = $this->getClientsData($request);
            $janischaClients = $this->getClientsData($request);

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

        // 1. Get data from admin/reports/client-account (Janischa) - Using same logic as ReportController
        try {
            Log::info('=== Fetching admin/reports/client-account data (Janischa) ===');
            
            // Use same logic as ReportController - try API first, then database
            $dataSource = 'Database';
            $apiError = null;
            $userEmail = 'Janischa.trade@gmail.com';
            
            // Check cache first
            $cacheKey = 'janischa_client_account_data_' . md5($userEmail);
            $cachedData = Cache::get($cacheKey);
            
            if ($cachedData && !$request->has('refresh')) {
                Log::info('Using cached Janischa client account data');
                $clients = collect($cachedData);
                $dataSource = 'Cached API';
            } else {
                try {
                    // Try to get data from Exness API first
                    $janischaExnessService = app(\App\Services\JanischaExnessAuthService::class);
                    $apiResponse = $janischaExnessService->getClientsData();
                    
                    if (isset($apiResponse['error'])) {
                        throw new \Exception($apiResponse['error']);
                    }
                    
                    $apiClients = $apiResponse['data'] ?? [];
                    $dataSource = 'Exness API';
                    
                    Log::info('Successfully fetched data from Exness API', [
                        'count' => count($apiClients),
                        'user' => 'Janischa.trade@gmail.com'
                    ]);
                    
                    // Cache the data for 5 minutes
                    Cache::put($cacheKey, $apiClients, 300);
                    
                    // Use API data
                    $clients = collect($apiClients);
                    
                } catch (\Exception $e) {
                    Log::warning('Failed to fetch from Exness API, using database data', [
                        'error' => $e->getMessage(),
                        'user' => 'Janischa.trade@gmail.com'
                    ]);
                    
                    $apiError = $e->getMessage();
                    
                    // Fallback to database
                    $query = JanischaClient::query();
                    
                    // Apply filters
                    if ($request->filled('partner_account')) {
                        $query->where('partner_account', 'like', '%' . $request->partner_account . '%');
                    }
                    if ($request->filled('client_uid')) {
                        $query->where('client_uid', 'like', '%' . $request->client_uid . '%');
                    }
                    if ($request->filled('client_country')) {
                        $query->where('client_country', $request->client_country);
                    }
                    if ($request->filled('reg_date')) {
                        $query->whereDate('reg_date', $request->reg_date);
                    }
                    
                    $clients = $query->get();
                    
                                    // Convert to API format for consistency
                $clients = $clients->map(function ($client) {
                    // ดึง client_account จาก raw_data สำหรับ Janischa
                    $clientAccount = null;
                    if ($client->raw_data && is_array($client->raw_data)) {
                        if (isset($client->raw_data['client_account']) && !empty($client->raw_data['client_account'])) {
                            $clientAccount = (string)$client->raw_data['client_account'];
                        } elseif (isset($client->raw_data['client_name']) && !empty($client->raw_data['client_name'])) {
                            $clientAccount = (string)$client->raw_data['client_name'];
                        }
                    }
                    
                    return [
                        'partner_account' => $client->partner_account,
                        'client_uid' => $client->client_uid,
                        'client_account' => $clientAccount ?? $client->client_id ?? $client->client_uid ?? null,
                        'client_name' => $clientAccount ?? $client->client_id ?? $client->client_uid ?? null,
                        'client_email' => $client->raw_data['client_email'] ?? null,
                        'client_id' => $client->client_id ?? $client->client_uid ?? null,
                        'reg_date' => $client->reg_date,
                        'client_country' => $client->client_country,
                        'volume_lots' => $client->volume_lots,
                        'volume_mln_usd' => $client->volume_mln_usd,
                        'reward_usd' => $client->reward_usd,
                        'client_status' => $client->client_status,
                        'kyc_passed' => $client->kyc_passed,
                        'ftd_received' => $client->ftd_received,
                        'ftt_made' => $client->ftt_made,
                    ];
                });
                }
            }
            
            // Format client data with calculated fields (same as ReportController)
            $formattedJanischaClients = $clients->map(function ($client) {
                $volumeLots = (float)($client['volume_lots'] ?? 0);
                $rewardUsd = (float)($client['reward_usd'] ?? 0);
                
                // Calculate status based on activity
                $clientStatus = ($volumeLots > 0 || $rewardUsd > 0) ? 'ACTIVE' : 'INACTIVE';
                
                // KYC estimation based on activity level
                $kycPassed = ($volumeLots > 1.0 || $rewardUsd > 10.0) ? true : null;
                
                // Ensure country field is properly handled
                $clientCountry = $client['client_country'] ?? $client['country'] ?? '-';
                
                return [
                    'partner_account' => $client['partner_account'] ?? '-',
                    'client_uid' => $client['client_uid'] ?? '-',
                    'client_account' => $client['client_account'] ?? $client['client_id'] ?? $client['client_uid'] ?? '-',
                    'client_name' => $client['client_account'] ?? $client['client_id'] ?? $client['client_uid'] ?? null,
                    'client_email' => $client['client_email'] ?? null,
                    'client_id' => $client['client_id'] ?? $client['client_uid'] ?? null,
                    'reg_date' => $client['reg_date'],
                    'client_country' => $clientCountry,
                    'volume_lots' => $volumeLots,
                    'volume_mln_usd' => (float)($client['volume_mln_usd'] ?? 0),
                    'reward_usd' => $rewardUsd,
                    'client_status' => $clientStatus,
                    'kyc_passed' => $kycPassed,
                    'ftd_received' => ($volumeLots > 0 || $rewardUsd > 0),
                    'ftt_made' => ($volumeLots > 0),
                    'source' => 'admin/reports/client-account',
                    'data_source' => 'Janischa'
                ];
            });
            
            $all = $all->concat($formattedJanischaClients);
            $debugInfo['admin_reports_client_account'] = $formattedJanischaClients->count();
            Log::info('admin/reports/client-account (Janischa) clients count: ' . $formattedJanischaClients->count());
            
            // Debug: Log sample data
            if ($formattedJanischaClients->count() > 0) {
                Log::info('Sample admin/reports/client-account client:', $formattedJanischaClients->first());
            }
        } catch (\Throwable $e) {
            Log::error('Error fetching admin/reports/client-account data: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            $debugInfo['admin_reports_client_account'] = 0;
        }

        // 2. Get data from admin/reports1/client-account1 (Ham) - Using same logic as Report1Controller
        try {
            Log::info('=== Fetching admin/reports1/client-account1 data (Ham) ===');
            
            // Use same logic as Report1Controller - try API first, then database
            $dataSource = 'Database';
            $apiError = null;
            $userEmail = 'hamsftmo@gmail.com';
            
            try {
                // Try to get data from Exness API first
                $hamExnessAuthService = app(\App\Services\HamExnessAuthService::class);
                $apiResponse = $hamExnessAuthService->getClientsData();
                
                if (isset($apiResponse['error'])) {
                    throw new \Exception($apiResponse['error']);
                }
                
                $apiClients = $apiResponse['data'] ?? [];
                $dataSource = 'Exness API';
                
                Log::info('Successfully fetched data from Exness API', [
                    'count' => count($apiClients),
                    'user' => 'hamsftmo@gmail.com'
                ]);
                
                // Use API data
                $clients = collect($apiClients);
                
            } catch (\Exception $e) {
                Log::warning('Failed to fetch from Exness API, using database data', [
                    'error' => $e->getMessage(),
                    'user' => 'hamsftmo@gmail.com'
                ]);
                
                $apiError = $e->getMessage();
                
                // Fallback to database
                $query = HamClient::query();
                $clients = $query->get();
                
                // Convert to API format for consistency
                $clients = $clients->map(function ($client) {
                    return [
                        'partner_account' => $client->partner_account,
                        'client_uid' => $client->client_uid,
                        'reg_date' => $client->reg_date,
                        'client_country' => $client->client_country,
                        'volume_lots' => $client->volume_lots,
                        'volume_mln_usd' => $client->volume_mln_usd,
                        'reward_usd' => $client->reward_usd,
                        'client_status' => $client->client_status,
                        'kyc_passed' => $client->kyc_passed,
                        'ftd_received' => $client->ftd_received,
                        'ftt_made' => $client->ftt_made,
                    ];
                });
            }
            
            // Format client data with calculated fields (same as Report1Controller)
            $formattedHamClients = $clients->map(function ($client) {
                $volumeLots = (float) ($client['volume_lots'] ?? 0);
                $rewardUsd = (float) ($client['reward_usd'] ?? 0);
                
                // Calculate status based on activity
                $clientStatus = ($volumeLots > 0 || $rewardUsd > 0) ? 'ACTIVE' : 'INACTIVE';
                
                // KYC estimation based on activity level
                $kycPassed = ($volumeLots > 1.0 || $rewardUsd > 10.0) ? true : null;
                
                return [
                    'partner_account' => $client['partner_account'] ?? '-',
                    'client_uid' => $client['client_uid'] ?? '-',
                    'client_account' => $client['client_account'] ?? '-',
                    'reg_date' => $client['reg_date'],
                    'client_country' => $client['client_country'] ?? '-',
                    'volume_lots' => $volumeLots,
                    'volume_mln_usd' => (float) ($client['volume_mln_usd'] ?? 0),
                    'reward_usd' => $rewardUsd,
                    'client_status' => $clientStatus,
                    'kyc_passed' => $kycPassed,
                    'ftd_received' => ($volumeLots > 0 || $rewardUsd > 0),
                    'ftt_made' => ($volumeLots > 0),
                    'source' => 'admin/reports1/client-account1',
                    'data_source' => 'Ham'
                ];
            });
            
            $all = $all->concat($formattedHamClients);
            $debugInfo['admin_reports1_client_account1'] = $formattedHamClients->count();
            Log::info('admin/reports1/client-account1 (Ham) clients count: ' . $formattedHamClients->count());
            
            // Debug: Log sample data
            if ($formattedHamClients->count() > 0) {
                Log::info('Sample admin/reports1/client-account1 client:', $formattedHamClients->first());
            }
        } catch (\Throwable $e) {
            Log::error('Error fetching admin/reports1/client-account1 data: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            $debugInfo['admin_reports1_client_account1'] = 0;
        }

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
