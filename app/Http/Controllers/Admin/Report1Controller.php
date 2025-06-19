<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use App\Services\HamExnessAuthService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Report1Controller extends Controller
{
    protected $hamExnessAuthService;

    public function __construct(HamExnessAuthService $hamExnessAuthService)
    {
        $this->hamExnessAuthService = $hamExnessAuthService;
    }

    public function clients1(Request $request)
    {
        try {
            Log::info('Fetching clients data for reports1/clients1...');
            
            // Get Ham user
            $hamUser = User::where('email', 'hamsftmo@gmail.com')->first();
            
            // Try to get data from Exness API first
            $apiData = null;
            $dataSource = 'database';
            $error = null;
            
            if ($hamUser) {
                try {
                    $result = $this->hamExnessAuthService->getClientsData();
                    Log::info('Ham API result:', ['result_type' => gettype($result), 'result_keys' => is_array($result) ? array_keys($result) : 'not_array']);
                    
                    if (isset($result['data']) && is_array($result['data']) && !empty($result['data'])) {
                        $apiData = $result['data'];
                        $dataSource = 'exness_api';
                        Log::info('Using Exness API data', ['count' => count($apiData)]);
                    } else {
                        Log::warning('API data not available', ['result' => $result]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to fetch from Exness API: ' . $e->getMessage());
                    $error = 'ไม่สามารถเชื่อมต่อ Exness API ได้ กำลังใช้ข้อมูลจาก Database';
                }
            }

            $filters = [];
            
            if ($dataSource === 'exness_api' && $apiData) {
                // Use API data (V1 only - exact match with Exness)
                $clients = collect($apiData);
                
                Log::info('Raw V1 API data (should match Exness exactly):', [
                    'total_count' => $clients->count(),
                    'unique_client_uids' => $clients->unique('client_uid')->count(),
                    'total_volume_lots' => $clients->sum('volume_lots'),
                    'total_volume_usd' => $clients->sum('volume_mln_usd'),
                    'total_reward_usd' => $clients->sum('reward_usd'),
                    'sample_records' => $clients->take(3)->toArray()
                ]);
                
                // Apply filters
                if ($request->filled('search')) {
                    $clients = $clients->filter(function ($client) use ($request) {
                        return stripos($client['client_uid'] ?? '', $request->search) !== false;
                    });
                    $filters['search'] = $request->search;
                }

                if ($request->filled('status') && $request->status !== 'all') {
                    $clients = $clients->filter(function ($client) use ($request) {
                        return strtoupper($client['client_status'] ?? '') === strtoupper($request->status);
                    });
                    $filters['status'] = $request->status;
                }

                if ($request->filled('start_date')) {
                    $clients = $clients->filter(function ($client) use ($request) {
                        $regDate = $client['reg_date'] ?? null;
                        return $regDate && $regDate >= $request->start_date;
                    });
                    $filters['start_date'] = $request->start_date;
                }

                if ($request->filled('end_date')) {
                    $clients = $clients->filter(function ($client) use ($request) {
                        $regDate = $client['reg_date'] ?? null;
                        return $regDate && $regDate <= $request->end_date;
                    });
                    $filters['end_date'] = $request->end_date;
                }

                // Calculate stats directly from filtered data (no grouping needed)
                $totalVolumeLots = $clients->sum('volume_lots');
                $totalVolumeMlnUsd = $clients->sum('volume_mln_usd');
                $totalRewardUsd = $clients->sum('reward_usd');
                $uniqueClientUids = $clients->unique('client_uid')->count();
                
                Log::info('Final stats calculation (should match Exness):', [
                    'filtered_count' => $clients->count(),
                    'unique_client_uids' => $uniqueClientUids,
                    'total_volume_lots' => $totalVolumeLots,
                    'total_volume_mln_usd' => $totalVolumeMlnUsd,
                    'total_reward_usd' => $totalRewardUsd
                ]);
                
                $stats = [
                    'total_pending' => $uniqueClientUids,
                    'total_amount' => $totalVolumeLots,
                    'due_today' => $totalVolumeMlnUsd,
                    'overdue' => $totalRewardUsd,
                    'total_client_uids' => $uniqueClientUids
                ];

                // Format client data for display (group by client_uid for display purposes only)
                $groupedClients = $clients->groupBy('client_uid')->map(function ($group) {
                    $first = $group->first();
                    return [
                        'client_uid' => $first['client_uid'] ?? '-',
                        'client_status' => $group->max('client_status') ?? 'UNKNOWN',
                        'reward_usd' => (float)$group->sum('reward_usd'),
                        'rebate_amount_usd' => 0, // V1 API doesn't have this field
                        'volume_lots' => (float)$group->sum('volume_lots'),
                        'volume_mln_usd' => (float)$group->sum('volume_mln_usd'),
                        'reg_date' => $group->max('reg_date'),
                        'partner_account' => $group->max('partner_account') ?? '-',
                        'client_country' => $group->max('client_country') ?? '-'
                    ];
                })->values();

                $formattedClients = $groupedClients;

            } else {
                // Use database data (same as original ClientController)
                $query = Client::query();

                if ($request->filled('search')) {
                    $query->where('client_uid', 'like', '%' . $request->search . '%');
                    $filters['search'] = $request->search;
                }

                if ($request->filled('status') && $request->status !== 'all') {
                    $query->where('client_status', $request->status);
                    $filters['status'] = $request->status;
                }

                if ($request->filled('start_date')) {
                    $query->whereDate('reg_date', '>=', $request->start_date);
                    $filters['start_date'] = $request->start_date;
                }

                if ($request->filled('end_date')) {
                    $query->whereDate('reg_date', '<=', $request->end_date);
                    $filters['end_date'] = $request->end_date;
                }

                // Get unique clients grouped by client_uid for display
                $clients = $query
                    ->selectRaw('
                        client_uid,
                        MAX(client_status) as client_status,
                        SUM(reward_usd) as total_reward_usd,
                        SUM(rebate_amount_usd) as total_rebate_amount_usd,
                        SUM(volume_lots) as total_volume_lots,
                        SUM(volume_mln_usd) as total_volume_mln_usd,
                        MAX(reg_date) as reg_date,
                        MAX(partner_account) as partner_account,
                        MAX(client_country) as client_country
                    ')
                    ->groupBy('client_uid')
                    ->orderBy('reg_date', 'desc')
                    ->get();

                // Calculate statistics from the same filtered query
                $statsQuery = Client::query();
                
                if ($request->filled('search')) {
                    $statsQuery->where('client_uid', 'like', '%' . $request->search . '%');
                }
                if ($request->filled('status') && $request->status !== 'all') {
                    $statsQuery->where('client_status', $request->status);
                }
                if ($request->filled('start_date')) {
                    $statsQuery->whereDate('reg_date', '>=', $request->start_date);
                }
                if ($request->filled('end_date')) {
                    $statsQuery->whereDate('reg_date', '<=', $request->end_date);
                }

                $aggregatedStats = $statsQuery
                    ->selectRaw('
                        client_uid,
                        SUM(volume_lots) as total_volume_lots,
                        SUM(volume_mln_usd) as total_volume_usd,
                        SUM(reward_usd) as total_reward_usd
                    ')
                    ->groupBy('client_uid')
                    ->get();

                $stats = [
                    'total_pending' => $aggregatedStats->count(),
                    'total_amount' => $aggregatedStats->sum('total_volume_lots'),
                    'due_today' => $aggregatedStats->sum('total_volume_usd'),
                    'overdue' => $aggregatedStats->sum('total_reward_usd'),
                    'total_client_uids' => $aggregatedStats->count()
                ];

                // Format client data
                $formattedClients = $clients->map(function ($client) {
                    return [
                        'client_uid' => $client->client_uid ?? '-',
                        'client_status' => $client->client_status ?? 'UNKNOWN',
                        'reward_usd' => (float)($client->total_reward_usd ?? 0),
                        'rebate_amount_usd' => (float)($client->total_rebate_amount_usd ?? 0),
                        'volume_lots' => (float)($client->total_volume_lots ?? 0),
                        'volume_mln_usd' => (float)($client->total_volume_mln_usd ?? 0),
                        'reg_date' => $client->reg_date,
                        'partner_account' => $client->partner_account ?? '-',
                        'client_country' => $client->client_country ?? '-'
                    ];
                });
            }

            Log::info('Data formatted successfully', ['count' => $formattedClients->count()]);

            if ($request->wantsJson()) {
                return response()->json([
                    'data' => [
                        'clients' => $formattedClients,
                        'stats' => $stats,
                        'data_source' => $dataSource,
                        'user_email' => 'hamsftmo@gmail.com'
                    ]
                ]);
            }

            return Inertia::render('Admin/Report1/Clients1', [
                'clients' => $formattedClients,
                'stats' => $stats,
                'filters' => $filters,
                'data_source' => $dataSource,
                'user_email' => 'hamsftmo@gmail.com',
                'error' => $error
            ]);

        } catch (\Exception $e) {
            Log::error('Error in Report1Controller@clients1: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage()
                ], 500);
            }

            return Inertia::render('Admin/Report1/Clients1', [
                'clients' => collect([]),
                'stats' => [
                    'total_pending' => 0,
                    'total_amount' => 0,
                    'due_today' => 0,
                    'overdue' => 0,
                    'total_client_uids' => 0
                ],
                'filters' => $filters,
                'data_source' => 'database',
                'user_email' => 'hamsftmo@gmail.com',
                'error' => 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage()
            ]);
        }
    }

    public function clientAccount1(Request $request)
    {
        try {
            Log::info('Fetching client account data for reports1/client-account1...');
            
            // Get Ham user
            $hamUser = User::where('email', 'hamsftmo@gmail.com')->first();
            
            // Try to get data from Exness API first
            $apiData = null;
            $dataSource = 'database';
            $error = null;
            
            if ($hamUser) {
                try {
                    $result = $this->hamExnessAuthService->getClientsData();
                    Log::info('Ham API result (clientAccount1):', ['result_type' => gettype($result), 'result_keys' => is_array($result) ? array_keys($result) : 'not_array']);
                    
                    if (isset($result['data']) && is_array($result['data']) && !empty($result['data'])) {
                        $apiData = $result['data'];
                        $dataSource = 'exness_api';
                        Log::info('Using Exness API data (clientAccount1)', ['count' => count($apiData)]);
                    } else {
                        Log::warning('API data not available (clientAccount1)', ['result' => $result]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to fetch from Exness API (clientAccount1): ' . $e->getMessage());
                    $error = 'ไม่สามารถเชื่อมต่อ Exness API ได้ กำลังใช้ข้อมูลจาก Database';
                }
            }

            $filters = [];
            
            if ($dataSource === 'exness_api' && $apiData) {
                // Use API data
                $clients = collect($apiData);
                
                // Apply filters
                if ($request->filled('search')) {
                    $clients = $clients->filter(function ($client) use ($request) {
                        return stripos($client['client_uid'] ?? '', $request->search) !== false;
                    });
                    $filters['search'] = $request->search;
                }

                if ($request->filled('status') && $request->status !== 'all') {
                    $clients = $clients->filter(function ($client) use ($request) {
                        return strtoupper($client['client_status'] ?? '') === strtoupper($request->status);
                    });
                    $filters['status'] = $request->status;
                }

                if ($request->filled('start_date')) {
                    $clients = $clients->filter(function ($client) use ($request) {
                        $regDate = $client['reg_date'] ?? null;
                        return $regDate && $regDate >= $request->start_date;
                    });
                    $filters['start_date'] = $request->start_date;
                }

                if ($request->filled('end_date')) {
                    $clients = $clients->filter(function ($client) use ($request) {
                        $regDate = $client['reg_date'] ?? null;
                        return $regDate && $regDate <= $request->end_date;
                    });
                    $filters['end_date'] = $request->end_date;
                }

                // Calculate stats
                $stats = [
                    'total_accounts' => $clients->count(),
                    'total_volume_lots' => $clients->sum('volume_lots'),
                    'total_volume_usd' => $clients->sum('volume_mln_usd'),
                    'total_profit' => $clients->sum('reward_usd'),
                    'total_client_uids' => $clients->unique('client_uid')->count()
                ];

                // Format client data for client account view
                $formattedClients = $clients->map(function ($client) {
                    return [
                        'partner_account' => $client['partner_account'] ?? '-',
                        'client_uid' => $client['client_uid'] ?? '-',
                        'reg_date' => $client['reg_date'],
                        'client_country' => $client['client_country'] ?? '-',
                        'volume_lots' => (float)($client['volume_lots'] ?? 0),
                        'volume_mln_usd' => (float)($client['volume_mln_usd'] ?? 0),
                        'reward_usd' => (float)($client['reward_usd'] ?? 0),
                        'client_status' => $client['client_status'] ?? 'UNKNOWN',
                        'kyc_passed' => (bool)($client['kyc_passed'] ?? false),
                        'ftd_received' => (bool)($client['ftd_received'] ?? false),
                        'ftt_made' => (bool)($client['ftt_made'] ?? false)
                    ];
                });

            } else {
                // Use database data (same as original ClientController)
                $query = Client::query();

                if ($request->filled('search')) {
                    $query->where('client_uid', 'like', '%' . $request->search . '%');
                    $filters['search'] = $request->search;
                }

                if ($request->filled('status') && $request->status !== 'all') {
                    $query->where('client_status', $request->status);
                    $filters['status'] = $request->status;
                }

                if ($request->filled('start_date')) {
                    $query->whereDate('reg_date', '>=', $request->start_date);
                    $filters['start_date'] = $request->start_date;
                }

                if ($request->filled('end_date')) {
                    $query->whereDate('reg_date', '<=', $request->end_date);
                    $filters['end_date'] = $request->end_date;
                }

                $clients = $query->orderBy('reg_date', 'desc')->get();

                // Calculate statistics
                $stats = [
                    'total_accounts' => $clients->count(),
                    'total_volume_lots' => $clients->sum('volume_lots'),
                    'total_volume_usd' => $clients->sum('volume_mln_usd'),
                    'total_profit' => $clients->sum('reward_usd'),
                    'total_client_uids' => $clients->unique('client_uid')->count()
                ];

                // Format client data
                $formattedClients = $clients->map(function ($client) {
                    return [
                        'partner_account' => $client->partner_account ?? '-',
                        'client_uid' => $client->client_uid ?? '-',
                        'reg_date' => $client->reg_date,
                        'client_country' => $client->client_country ?? '-',
                        'volume_lots' => (float)($client->volume_lots ?? 0),
                        'volume_mln_usd' => (float)($client->volume_mln_usd ?? 0),
                        'reward_usd' => (float)($client->reward_usd ?? 0),
                        'client_status' => $client->client_status ?? 'UNKNOWN',
                        'kyc_passed' => (bool)$client->kyc_passed,
                        'ftd_received' => (bool)$client->ftd_received,
                        'ftt_made' => (bool)$client->ftt_made
                    ];
                });
            }

            Log::info('Data formatted successfully');

            if ($request->wantsJson()) {
                return response()->json([
                    'data' => [
                        'clients' => $formattedClients,
                        'stats' => $stats,
                        'data_source' => $dataSource,
                        'user_email' => 'hamsftmo@gmail.com'
                    ]
                ]);
            }

            return Inertia::render('Admin/Report1/ClientAccount1', [
                'clients' => $formattedClients,
                'stats' => $stats,
                'filters' => $filters,
                'data_source' => $dataSource,
                'user_email' => 'hamsftmo@gmail.com',
                'error' => $error
            ]);

        } catch (\Exception $e) {
            Log::error('Error in Report1Controller@clientAccount1: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage()
                ], 500);
            }

            return Inertia::render('Admin/Report1/ClientAccount1', [
                'clients' => collect([]),
                'stats' => [
                    'total_accounts' => 0,
                    'total_volume_lots' => 0,
                    'total_volume_usd' => 0,
                    'total_profit' => 0,
                    'total_client_uids' => 0
                ],
                'filters' => $filters,
                'data_source' => 'database',
                'user_email' => 'hamsftmo@gmail.com',
                'error' => 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage()
            ]);
        }
    }

    public function clientTransaction1(Request $request)
    {
        // For now, return empty data - this would need transaction-specific API endpoints
        return Inertia::render('Admin/Report1/ClientTransaction1', [
            'transactions' => collect([]),
            'stats' => [
                'total_transactions' => 0,
                'total_amount' => 0,
                'pending_count' => 0,
                'completed_count' => 0
            ],
            'filters' => [],
            'data_source' => 'database',
            'user_email' => 'hamsftmo@gmail.com',
            'error' => null
        ]);
    }

    public function transactionsPending1(Request $request)
    {
        // For now, return empty data - this would need transaction-specific API endpoints
        return Inertia::render('Admin/Report1/TransactionsPending1', [
            'transactions' => collect([]),
            'stats' => [
                'total_pending' => 0,
                'total_amount' => 0,
                'due_today' => 0,
                'overdue' => 0
            ],
            'filters' => [],
            'data_source' => 'database',
            'user_email' => 'hamsftmo@gmail.com',
            'error' => null
        ]);
    }

    public function rewardHistory1(Request $request)
    {
        // For now, return empty data - this would need reward-specific API endpoints
        return Inertia::render('Admin/Report1/RewardHistory1', [
            'rewards' => collect([]),
            'stats' => [
                'total_rewards' => 0,
                'total_amount' => 0,
                'this_month' => 0,
                'last_month' => 0
            ],
            'filters' => [],
            'data_source' => 'database',
            'user_email' => 'hamsftmo@gmail.com',
            'error' => null
        ]);
    }

    public function testConnection()
    {
        try {
            $hamUser = User::where('email', 'hamsftmo@gmail.com')->first();
            
            if (!$hamUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ham user not found'
                ]);
            }

            $data = $this->hamExnessAuthService->getClientsData();
            
            return response()->json([
                'success' => true,
                'message' => 'Connection successful',
                'data_count' => count($data),
                'user_email' => 'hamsftmo@gmail.com',
                'sample_data' => array_slice($data, 0, 3) // Show first 3 records
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'user_email' => 'hamsftmo@gmail.com'
            ]);
        }
    }
} 