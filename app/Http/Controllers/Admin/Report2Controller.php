<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\KantapongExnessAuthService;
use App\Models\KantapongClient;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class Report2Controller extends Controller
{
    protected $kantapongExnessService;

    public function __construct(KantapongExnessAuthService $kantapongExnessService)
    {
        $this->kantapongExnessService = $kantapongExnessService;
    }

    public function clients2(Request $request)
    {
        try {
            Log::info('Report2Controller@clients2 started', [
                'request_params' => $request->all(),
                'user_email' => 'kantapong0592@gmail.com'
            ]);

            // Try to get data from Exness API first, fallback to database
            $dataSource = 'Database';
            $apiError = null;
            $userEmail = 'kantapong0592@gmail.com';

            // Check cache first to avoid repeated API calls
            $cacheKey = 'kantapong_clients2_data_' . md5($userEmail);
            $cachedData = \Cache::get($cacheKey);

            if ($cachedData && !$request->has('refresh')) {
                Log::info('Using cached Kantapong clients2 data');
                $clients = collect($cachedData);
                $dataSource = 'Cached API';
            } else {
                Log::info('No cached data, calling KantapongExnessAuthService...');
                try {
                    $apiResponse = $this->kantapongExnessService->getClientsData();

                    Log::info('KantapongExnessAuthService response received', [
                        'has_error' => isset($apiResponse['error']),
                        'has_data' => isset($apiResponse['data']),
                        'data_count' => isset($apiResponse['data']) ? count($apiResponse['data']) : 0
                    ]);

                    if (isset($apiResponse['error'])) {
                        Log::error('KantapongExnessAuthService returned error', ['error' => $apiResponse['error']]);
                        throw new \Exception($apiResponse['error']);
                    }

                    $apiClients = $apiResponse['data'] ?? [];
                    $dataSource = 'Exness API';

                    Log::info('Successfully fetched data from Exness API', [
                        'count' => count($apiClients),
                        'user' => 'kantapong0592@gmail.com'
                    ]);

                    // Cache the data for 5 minutes to reduce API calls
                    \Cache::put($cacheKey, $apiClients, 300);

                    // Use API data
                    $clients = collect($apiClients);

                } catch (\Exception $e) {
                    Log::error('Exception in KantapongExnessAuthService call', [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);

                    Log::warning('Failed to fetch from Exness API, using database data', [
                        'error' => $e->getMessage(),
                        'user' => 'kantapong0592@gmail.com'
                    ]);

                    $apiError = $e->getMessage();

                    // Fallback to database with pagination
                    $query = KantapongClient::query();

                    // Apply filters at database level for better performance
                    if ($request->filled('search')) {
                        $query->where('client_uid', 'like', '%' . $request->search . '%');
                    }
                    if ($request->filled('status') && $request->status !== 'all') {
                        $query->where('client_status', $request->status);
                    }
                    if ($request->filled('start_date')) {
                        $query->whereDate('reg_date', '>=', $request->start_date);
                    }
                    if ($request->filled('end_date')) {
                        $query->whereDate('reg_date', '<=', $request->end_date);
                    }

                    // Use pagination at database level
                    $perPage = 50; // Increased from 10 for better UX
                    $clients = $query->paginate($perPage);

                    Log::info('Using database fallback', [
                        'total_records' => $clients->total(),
                        'per_page' => $perPage
                    ]);

                    // Convert to API format for consistency
                    $clients->getCollection()->transform(function ($client) {
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

                    // Calculate stats efficiently
                    $statsQuery = KantapongClient::query();
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

                    $stats = [
                        'total_pending' => $statsQuery->count(),
                        'total_amount' => $statsQuery->sum('volume_lots'),
                        'due_today' => $statsQuery->sum('volume_mln_usd'),
                        'overdue' => $statsQuery->sum('reward_usd'),
                        'total_client_uids' => $statsQuery->distinct('client_uid')->count()
                    ];

                    return Inertia::render('Admin/Report2/Clients2', [
                        'clients' => $clients,
                        'stats' => $stats,
                        'filters' => $request->only(['search', 'status', 'start_date', 'end_date']),
                        'data_source' => $dataSource,
                        'user_email' => $userEmail,
                        'error' => $apiError
                    ]);
                }
            }

            // Apply filters (only for API data)
            $filters = [];

            if ($request->filled('search')) {
                $clients = $clients->filter(function ($client) use ($request) {
                    return stripos($client['client_uid'], $request->search) !== false;
                });
                $filters['search'] = $request->search;
            }

            if ($request->filled('status') && $request->status !== 'all') {
                $clients = $clients->filter(function ($client) use ($request) {
                    $volumeLots = (float)($client['volume_lots'] ?? 0);
                    $rewardUsd = (float)($client['reward_usd'] ?? 0);
                    $calculatedStatus = ($volumeLots > 0 || $rewardUsd > 0) ? 'ACTIVE' : 'INACTIVE';
                    return strtoupper($calculatedStatus) === strtoupper($request->status);
                });
                $filters['status'] = $request->status;
            }

            if ($request->filled('start_date')) {
                $clients = $clients->filter(function ($client) use ($request) {
                    return $client['reg_date'] >= $request->start_date;
                });
                $filters['start_date'] = $request->start_date;
            }

            if ($request->filled('end_date')) {
                $clients = $clients->filter(function ($client) use ($request) {
                    return $client['reg_date'] <= $request->end_date;
                });
                $filters['end_date'] = $request->end_date;
            }

            // Group by client_uid for unique display
            $uniqueClients = $clients->groupBy('client_uid')->map(function ($group) {
                $client = $group->first();
                return [
                    'client_uid' => $client['client_uid'] ?? '-',
                    'client_status' => $client['client_status'] ?? 'UNKNOWN',
                    'reward_usd' => $group->sum('reward_usd'),
                    'rebate_amount_usd' => 0, // Not available in Kantapong data
                    'volume_lots' => $group->sum('volume_lots'),
                    'volume_mln_usd' => $group->sum('volume_mln_usd'),
                    'reg_date' => $client['reg_date'] ?? null,
                    'partner_account' => $client['partner_account'] ?? '-',
                    'client_country' => $client['client_country'] ?? '-',
                ];
            })->values();

            // Calculate statistics efficiently
            $stats = [
                'total_pending' => $uniqueClients->count(),
                'total_amount' => $uniqueClients->sum('volume_lots'),
                'due_today' => $uniqueClients->sum('volume_mln_usd'),
                'overdue' => $uniqueClients->sum('reward_usd'),
                'total_client_uids' => $uniqueClients->count()
            ];

            // Format client data with calculated status
            $formattedClients = $uniqueClients->map(function ($client) {
                $volumeLots = (float)($client['volume_lots'] ?? 0);
                $rewardUsd = (float)($client['reward_usd'] ?? 0);

                // Calculate status based on activity
                $clientStatus = ($volumeLots > 0 || $rewardUsd > 0) ? 'ACTIVE' : 'INACTIVE';

                return [
                    'client_uid' => $client['client_uid'] ?? '-',
                    'client_status' => $clientStatus,
                    'reward_usd' => $rewardUsd,
                    'rebate_amount_usd' => (float)($client['rebate_amount_usd'] ?? 0),
                    'volume_lots' => $volumeLots,
                    'volume_mln_usd' => (float)($client['volume_mln_usd'] ?? 0),
                    'reg_date' => $client['reg_date'],
                    'partner_account' => $client['partner_account'] ?? '-',
                    'client_country' => $client['client_country'] ?? '-'
                ];
            });

            Log::info('Kantapong data formatted successfully', [
                'count' => $formattedClients->count(),
                'data_source' => $dataSource
            ]);

            // Convert to paginated collection with better performance
            $perPage = 50; // Increased from 10 for better UX
            $currentPage = (int) $request->get('page', 1);
            if ($currentPage < 1) {
                $currentPage = 1;
            }
            $offset = ($currentPage - 1) * $perPage;
            $paginatedClients = $formattedClients->slice($offset, $perPage)->values();

            // Create pagination data manually
            $pagination = [
                'data' => $paginatedClients,
                'current_page' => $currentPage,
                'per_page' => $perPage,
                'total' => $formattedClients->count(),
                'last_page' => ceil($formattedClients->count() / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $formattedClients->count()),
                'prev_page_url' => $currentPage > 1 ? $request->fullUrlWithQuery(['page' => $currentPage - 1]) : null,
                'next_page_url' => $currentPage < ceil($formattedClients->count() / $perPage) ? $request->fullUrlWithQuery(['page' => $currentPage + 1]) : null,
                'links' => []
            ];

            // Generate pagination links
            $totalPages = ceil($formattedClients->count() / $perPage);
            $pagination['links'][] = ['url' => $pagination['prev_page_url'], 'label' => '&laquo; Previous', 'active' => false];

            for ($i = 1; $i <= $totalPages; $i++) {
                $pagination['links'][] = [
                    'url' => $request->fullUrlWithQuery(['page' => $i]),
                    'label' => (string)$i,
                    'active' => $i == $currentPage
                ];
            }

            $pagination['links'][] = ['url' => $pagination['next_page_url'], 'label' => 'Next &raquo;', 'active' => false];

            Log::info('Report2Controller@clients2 completed successfully', [
                'final_data_source' => $dataSource,
                'final_count' => $formattedClients->count()
            ]);

            return Inertia::render('Admin/Report2/Clients2', [
                'clients' => $pagination,
                'stats' => $stats,
                'filters' => $filters,
                'data_source' => $dataSource,
                'user_email' => $userEmail,
                'error' => $apiError
            ]);

        } catch (\Exception $e) {
            Log::error('Error in Report2Controller@clients2: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return Inertia::render('Admin/Report2/Clients2', [
                'clients' => collect([]),
                'stats' => [
                    'total_pending' => 0,
                    'total_amount' => 0,
                    'due_today' => 0,
                    'overdue' => 0,
                    'total_client_uids' => 0
                ],
                'filters' => [],
                'data_source' => 'error',
                'user_email' => 'kantapong0592@gmail.com',
                'error' => 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage()
            ]);
        }
    }

    public function clientAccount2(Request $request)
    {
        try {
            Log::info('Fetching Kantapong client account data...');

            // Try to get data from Exness API first, fallback to database
            $dataSource = 'Database';
            $apiError = null;
            $userEmail = 'kantapong0592@gmail.com';

            // Check cache first to avoid repeated API calls
            $cacheKey = 'kantapong_clients_data_' . md5($userEmail);
            $cachedData = \Cache::get($cacheKey);

            if ($cachedData && !$request->has('refresh')) {
                Log::info('Using cached Kantapong data');
                $clients = collect($cachedData);
                $dataSource = 'Cached API';
            } else {
                try {
                    $apiResponse = $this->kantapongExnessService->getClientsData();

                    if (isset($apiResponse['error'])) {
                        throw new \Exception($apiResponse['error']);
                    }

                    $apiClients = $apiResponse['data'] ?? [];
                    $dataSource = 'Exness API';

                    Log::info('Successfully fetched data from Exness API', [
                        'count' => count($apiClients),
                        'user' => 'kantapong0592@gmail.com'
                    ]);

                    // Cache the data for 5 minutes to reduce API calls
                    \Cache::put($cacheKey, $apiClients, 300);

                    // Use API data
                    $clients = collect($apiClients);

                } catch (\Exception $e) {
                    Log::warning('Failed to fetch from Exness API, using database data', [
                        'error' => $e->getMessage(),
                        'user' => 'kantapong0592@gmail.com'
                    ]);

                    $apiError = $e->getMessage();

                    // Fallback to database with pagination
                    $query = KantapongClient::query();

                    // Apply filters at database level for better performance
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

                    // Use pagination at database level
                    $perPage = 50; // Increased from 10 for better UX
                    $clients = $query->paginate($perPage);

                    // Convert to API format for consistency
                    $clients->getCollection()->transform(function ($client) {
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

                    // Calculate stats efficiently
                    $statsQuery = KantapongClient::query();
                    if ($request->filled('partner_account')) {
                        $statsQuery->where('partner_account', 'like', '%' . $request->partner_account . '%');
                    }
                    if ($request->filled('client_uid')) {
                        $statsQuery->where('client_uid', 'like', '%' . $request->client_uid . '%');
                    }
                    if ($request->filled('client_country')) {
                        $statsQuery->where('client_country', $request->client_country);
                    }
                    if ($request->filled('reg_date')) {
                        $statsQuery->whereDate('reg_date', $request->reg_date);
                    }

                    $stats = [
                        'total_accounts' => $statsQuery->count(),
                        'total_volume_lots' => $statsQuery->sum('volume_lots'),
                        'total_volume_usd' => $statsQuery->sum('volume_mln_usd'),
                        'total_profit' => $statsQuery->sum('reward_usd'),
                        'total_client_uids' => $statsQuery->distinct('client_uid')->count()
                    ];

                    return Inertia::render('Admin/Report2/ClientAccount2', [
                        'clients' => $clients,
                        'stats' => $stats,
                        'filters' => $request->only(['partner_account', 'client_uid', 'client_country', 'reg_date']),
                        'data_source' => $dataSource,
                        'user_email' => $userEmail,
                        'error' => $apiError
                    ]);
                }
            }

            // Apply filters (only for API data)
            $filters = [];

            if ($request->filled('partner_account')) {
                $clients = $clients->filter(function ($client) use ($request) {
                    return stripos($client['partner_account'], $request->partner_account) !== false;
                });
                $filters['partner_account'] = $request->partner_account;
            }

            if ($request->filled('client_uid')) {
                $clients = $clients->filter(function ($client) use ($request) {
                    return stripos($client['client_uid'], $request->client_uid) !== false;
                });
                $filters['client_uid'] = $request->client_uid;
            }

            if ($request->filled('client_country')) {
                $clients = $clients->filter(function ($client) use ($request) {
                    return $client['client_country'] === $request->client_country;
                });
                $filters['client_country'] = $request->client_country;
            }

            if ($request->filled('client_status')) {
                $clients = $clients->filter(function ($client) use ($request) {
                    $volumeLots = (float)($client['volume_lots'] ?? 0);
                    $rewardUsd = (float)($client['reward_usd'] ?? 0);
                    $calculatedStatus = ($volumeLots > 0 || $rewardUsd > 0) ? 'ACTIVE' : 'INACTIVE';
                    return strtoupper($calculatedStatus) === strtoupper($request->client_status);
                });
                $filters['client_status'] = $request->client_status;
            }

            if ($request->filled('reg_date')) {
                $clients = $clients->filter(function ($client) use ($request) {
                    return $client['reg_date'] === $request->reg_date;
                });
                $filters['reg_date'] = $request->reg_date;
            }

            // Calculate statistics efficiently
            $stats = [
                'total_accounts' => $clients->count(),
                'total_volume_lots' => $clients->sum('volume_lots'),
                'total_volume_usd' => $clients->sum('volume_mln_usd'),
                'total_profit' => $clients->sum('reward_usd'),
                'total_client_uids' => $clients->pluck('client_uid')->unique()->count()
            ];

            // Format client data with calculated fields
            $formattedClients = $clients->map(function ($client) {
                $volumeLots = (float)($client['volume_lots'] ?? 0);
                $rewardUsd = (float)($client['reward_usd'] ?? 0);

                // Calculate status based on activity
                $clientStatus = ($volumeLots > 0 || $rewardUsd > 0) ? 'ACTIVE' : 'INACTIVE';

                // KYC estimation based on activity level
                $kycPassed = ($volumeLots > 1.0 || $rewardUsd > 10.0) ? true : null;

                return [
                    'partner_account' => $client['partner_account'] ?? '-',
                    'client_uid' => $client['client_uid'] ?? '-',
                    'reg_date' => $client['reg_date'],
                    'client_country' => $client['client_country'] ?? '-',
                    'volume_lots' => $volumeLots,
                    'volume_mln_usd' => (float)($client['volume_mln_usd'] ?? 0),
                    'reward_usd' => $rewardUsd,
                    'client_status' => $clientStatus,
                    'kyc_passed' => $kycPassed,
                    'ftd_received' => ($volumeLots > 0 || $rewardUsd > 0),
                    'ftt_made' => ($volumeLots > 0)
                ];
            });

            Log::info('Kantapong client account data formatted successfully', [
                'count' => $formattedClients->count(),
                'data_source' => $dataSource
            ]);

            // Convert to paginated collection with better performance
            $perPage = 50; // Increased for better UX
            $currentPage = (int) $request->get('page', 1);
            if ($currentPage < 1) {
                $currentPage = 1;
            }
            $offset = ($currentPage - 1) * $perPage;
            $paginatedClients = $formattedClients->slice($offset, $perPage)->values();

            // Create pagination data manually
            $pagination = [
                'data' => $paginatedClients,
                'current_page' => $currentPage,
                'per_page' => $perPage,
                'total' => $formattedClients->count(),
                'last_page' => ceil($formattedClients->count() / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $formattedClients->count()),
                'prev_page_url' => $currentPage > 1 ? $request->fullUrlWithQuery(['page' => $currentPage - 1]) : null,
                'next_page_url' => $currentPage < ceil($formattedClients->count() / $perPage) ? $request->fullUrlWithQuery(['page' => $currentPage + 1]) : null,
                'links' => []
            ];

            // Generate pagination links
            $totalPages = ceil($formattedClients->count() / $perPage);
            $pagination['links'][] = ['url' => $pagination['prev_page_url'], 'label' => '&laquo; Previous', 'active' => false];

            for ($i = 1; $i <= $totalPages; $i++) {
                $pagination['links'][] = [
                    'url' => $request->fullUrlWithQuery(['page' => $i]),
                    'label' => (string)$i,
                    'active' => $i == $currentPage
                ];
            }

            $pagination['links'][] = ['url' => $pagination['next_page_url'], 'label' => 'Next &raquo;', 'active' => false];

            return Inertia::render('Admin/Report2/ClientAccount2', [
                'clients' => $pagination,
                'stats' => $stats,
                'filters' => $filters,
                'data_source' => $dataSource,
                'user_email' => $userEmail,
                'error' => $apiError
            ]);

        } catch (\Exception $e) {
            Log::error('Error in Report2Controller@clientAccount2: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return Inertia::render('Admin/Report2/ClientAccount2', [
                'clients' => collect([]),
                'stats' => [
                    'total_accounts' => 0,
                    'total_volume_lots' => 0,
                    'total_volume_usd' => 0,
                    'total_profit' => 0,
                    'total_client_uids' => 0
                ],
                'filters' => [],
                'data_source' => 'Error',
                'user_email' => 'kantapong0592@gmail.com',
                'error' => 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage()
            ]);
        }
    }

    public function clientTransaction2(Request $request)
    {
        try {
            // Get data from Exness API
            $apiResult = $this->kantapongExnessService->getClientsData();

            if (isset($apiResult['error'])) {
                $transactions = [];
                $dataSource = 'error';
            } else {
                // Convert clients data to transaction format
                $transactions = collect($apiResult['data'])->map(function ($client) {
                    return [
                        'id' => $client['id'] ?? null,
                        'client_uid' => $client['client_uid'] ?? '',
                        'partner_account' => $client['partner_account'] ?? '',
                        'transaction_date' => $client['reg_date'] ?? '',
                        'volume_lots' => (float) ($client['volume_lots'] ?? 0),
                        'volume_usd' => (float) ($client['volume_mln_usd'] ?? 0),
                        'reward_usd' => (float) ($client['reward_usd'] ?? 0),
                        'client_country' => $client['client_country'] ?? '',
                        'status' => $client['client_status'] ?? 'UNKNOWN',
                    ];
                })->toArray();

                $dataSource = 'exness_api';
            }

            // Apply filters
            if ($request->filled('search')) {
                $search = strtolower($request->search);
                $transactions = array_filter($transactions, function ($transaction) use ($search) {
                    return strpos(strtolower($transaction['client_uid'] ?? ''), $search) !== false ||
                           strpos(strtolower($transaction['partner_account'] ?? ''), $search) !== false;
                });
            }

            // Calculate statistics
            $totalTransactions = count($transactions);
            $totalVolumeLots = array_sum(array_column($transactions, 'volume_lots'));
            $totalVolumeUsd = array_sum(array_column($transactions, 'volume_usd'));
            $totalRewardUsd = array_sum(array_column($transactions, 'reward_usd'));

            // Reset array keys
            $transactions = array_values($transactions);

            return Inertia::render('Admin/Report2/ClientTransaction2', [
                'transactions' => $transactions,
                'stats' => [
                    'total_transactions' => $totalTransactions,
                    'total_volume_lots' => $totalVolumeLots,
                    'total_volume_usd' => $totalVolumeUsd,
                    'total_reward_usd' => $totalRewardUsd,
                    'data_source' => $dataSource,
                ],
                'currentFilters' => [
                    'search' => $request->search ?? '',
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Kantapong Report2 client transactions error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Inertia::render('Admin/Report2/ClientTransaction2', [
                'transactions' => [],
                'stats' => [
                    'total_transactions' => 0,
                    'total_volume_lots' => 0,
                    'total_volume_usd' => 0,
                    'total_reward_usd' => 0,
                    'data_source' => 'error',
                ],
                'currentFilters' => [
                    'search' => '',
                ],
                'error' => 'เกิดข้อผิดพลาดในการโหลดข้อมูล: ' . $e->getMessage()
            ]);
        }
    }

    public function transactionsPending2()
    {
        try {
            // Get data from Exness API
            $apiResult = $this->kantapongExnessService->getClientsData();

            if (isset($apiResult['error'])) {
                $pendingTransactions = [];
                $dataSource = 'error';
            } else {
                // Filter for pending transactions (example logic)
                $pendingTransactions = collect($apiResult['data'])->filter(function ($client) {
                    return ($client['client_status'] ?? '') === 'PENDING' ||
                           !($client['kyc_passed'] ?? false);
                })->map(function ($client) {
                    return [
                        'id' => $client['id'] ?? null,
                        'client_uid' => $client['client_uid'] ?? '',
                        'partner_account' => $client['partner_account'] ?? '',
                        'reg_date' => $client['reg_date'] ?? '',
                        'volume_lots' => (float) ($client['volume_lots'] ?? 0),
                        'volume_usd' => (float) ($client['volume_mln_usd'] ?? 0),
                        'reward_usd' => (float) ($client['reward_usd'] ?? 0),
                        'status' => $client['client_status'] ?? 'PENDING',
                        'kyc_passed' => $client['kyc_passed'] ?? false,
                    ];
                })->values()->toArray();

                $dataSource = 'exness_api';
            }

            $totalPending = count($pendingTransactions);
            $totalVolumeLots = array_sum(array_column($pendingTransactions, 'volume_lots'));
            $totalRewardUsd = array_sum(array_column($pendingTransactions, 'reward_usd'));

            return Inertia::render('Admin/Report2/TransactionsPending2', [
                'transactions' => $pendingTransactions,
                'stats' => [
                    'total_pending' => $totalPending,
                    'total_volume_lots' => $totalVolumeLots,
                    'total_reward_usd' => $totalRewardUsd,
                    'data_source' => $dataSource,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Kantapong Report2 pending transactions error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Inertia::render('Admin/Report2/TransactionsPending2', [
                'transactions' => [],
                'stats' => [
                    'total_pending' => 0,
                    'total_volume_lots' => 0,
                    'total_reward_usd' => 0,
                    'data_source' => 'error',
                ],
                'error' => 'เกิดข้อผิดพลาดในการโหลดข้อมูล: ' . $e->getMessage()
            ]);
        }
    }

    public function rewardHistory2()
    {
        try {
            // Get data from Exness API
            $apiResult = $this->kantapongExnessService->getClientsData();

            if (isset($apiResult['error'])) {
                $rewardHistory = [];
                $dataSource = 'error';
            } else {
                // Convert to reward history format
                $rewardHistory = collect($apiResult['data'])->filter(function ($client) {
                    return ($client['reward_usd'] ?? 0) > 0;
                })->map(function ($client) {
                    return [
                        'id' => $client['id'] ?? null,
                        'client_uid' => $client['client_uid'] ?? '',
                        'partner_account' => $client['partner_account'] ?? '',
                        'reward_date' => $client['reg_date'] ?? '',
                        'reward_amount' => (float) ($client['reward_usd'] ?? 0),
                        'volume_lots' => (float) ($client['volume_lots'] ?? 0),
                        'client_country' => $client['client_country'] ?? '',
                        'status' => 'PAID',
                    ];
                })->values()->toArray();

                $dataSource = 'exness_api';
            }

            $totalRewards = count($rewardHistory);
            $totalAmount = array_sum(array_column($rewardHistory, 'reward_amount'));
            $totalVolume = array_sum(array_column($rewardHistory, 'volume_lots'));

            return Inertia::render('Admin/Report2/RewardHistory2', [
                'rewards' => $rewardHistory,
                'stats' => [
                    'total_rewards' => $totalRewards,
                    'total_amount' => $totalAmount,
                    'total_volume' => $totalVolume,
                    'data_source' => $dataSource,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Kantapong Report2 reward history error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Inertia::render('Admin/Report2/RewardHistory2', [
                'rewards' => [],
                'stats' => [
                    'total_rewards' => 0,
                    'total_amount' => 0,
                    'total_volume' => 0,
                    'data_source' => 'error',
                ],
                'error' => 'เกิดข้อผิดพลาดในการโหลดข้อมูล: ' . $e->getMessage()
            ]);
        }
    }

    public function testConnection2()
    {
        try {
            Log::info('Testing Kantapong API connection...');

            $service = new KantapongExnessAuthService();

            // Test authentication
            $token = $service->retrieveToken();

            if ($token) {
                Log::info('Kantapong API connection successful', [
                    'token_length' => strlen($token),
                    'token_preview' => substr($token, 0, 50) . '...'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Kantapong API connection successful',
                    'token_length' => strlen($token),
                    'token_preview' => substr($token, 0, 50) . '...'
                ]);
            } else {
                Log::error('Kantapong API connection failed - no token received');

                return response()->json([
                    'success' => false,
                    'message' => 'Kantapong API connection failed - no token received'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Kantapong API connection test error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Kantapong API connection test error: ' . $e->getMessage()
            ], 500);
        }
    }
}
