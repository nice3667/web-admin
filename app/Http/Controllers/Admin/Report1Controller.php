<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HamClient;
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
            Log::info('Fetching Ham clients data for reports1/clients1...');

            // Try to get data from Exness API first, fallback to database
            $dataSource = 'Database';
            $apiError = null;
            $userEmail = 'hamsftmo@gmail.com';

            try {
                $apiResponse = $this->hamExnessAuthService->getClientsData();

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

            // Apply filters
            $filters = [];

            if ($request->filled('search')) {
                $clients = $clients->filter(function ($client) use ($request) {
                    return stripos($client['client_uid'], $request->search) !== false;
                });
                $filters['search'] = $request->search;
            }

            if ($request->filled('status') && $request->status !== 'all') {
                $clients = $clients->filter(function ($client) use ($request) {
                    $volumeLots = (float) ($client['volume_lots'] ?? 0);
                    $rewardUsd = (float) ($client['reward_usd'] ?? 0);
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
                    'rebate_amount_usd' => 0, // Not available in Ham data
                    'volume_lots' => $group->sum('volume_lots'),
                    'volume_mln_usd' => $group->sum('volume_mln_usd'),
                    'reg_date' => $client['reg_date'] ?? null,
                    'partner_account' => $client['partner_account'] ?? '-',
                    'client_country' => $client['client_country'] ?? '-',
                ];
            })->values();

            // Calculate statistics
            $stats = [
                'total_pending' => $uniqueClients->count(),
                'total_amount' => $uniqueClients->sum('volume_lots'),
                'due_today' => $uniqueClients->sum('volume_mln_usd'),
                'overdue' => $uniqueClients->sum('reward_usd'),
                'total_client_uids' => $uniqueClients->count()
            ];

            // Format client data with calculated status
            $formattedClients = $uniqueClients->map(function ($client) {
                $volumeLots = (float) ($client['volume_lots'] ?? 0);
                $rewardUsd = (float) ($client['reward_usd'] ?? 0);

                // Calculate status based on activity
                $clientStatus = ($volumeLots > 0 || $rewardUsd > 0) ? 'ACTIVE' : 'INACTIVE';

                return [
                    'client_uid' => $client['client_uid'] ?? '-',
                    'client_status' => $clientStatus,
                    'reward_usd' => $rewardUsd,
                    'rebate_amount_usd' => (float) ($client['rebate_amount_usd'] ?? 0),
                    'volume_lots' => $volumeLots,
                    'volume_mln_usd' => (float) ($client['volume_mln_usd'] ?? 0),
                    'reg_date' => $client['reg_date'],
                    'partner_account' => $client['partner_account'] ?? '-',
                    'client_country' => $client['client_country'] ?? '-'
                ];
            });

            Log::info('Ham data formatted successfully', [
                'count' => $formattedClients->count(),
                'data_source' => $dataSource
            ]);

            // Send all data for client-side pagination
            $pagination = [
                'data' => $formattedClients->values(),
                'current_page' => 1,
                'per_page' => 10,
                'total' => $formattedClients->count(),
                'last_page' => 1,
                'from' => 1,
                'to' => $formattedClients->count(),
            ];



            return Inertia::render('Admin/Report1/Clients1', [
                'clients' => $pagination,
                'stats' => $stats,
                'filters' => $filters,
                'data_source' => $dataSource,
                'user_email' => $userEmail,
                'error' => $apiError
            ]);

        } catch (\Exception $e) {
            Log::error('Error in Report1Controller@clients1: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return Inertia::render('Admin/Report1/Clients1', [
                'clients' => collect([]),
                'stats' => [
                    'total_pending' => 0,
                    'total_amount' => 0,
                    'due_today' => 0,
                    'overdue' => 0,
                    'total_client_uids' => 0
                ],
                'filters' => [],
                'data_source' => 'Error',
                'user_email' => 'hamsftmo@gmail.com',
                'error' => 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage()
            ]);
        }
    }

    public function clientAccount1(Request $request)
    {
        try {
            Log::info('Fetching Ham client account data...');

            // Try to get data from Exness API first, fallback to database
            $dataSource = 'Database';
            $apiError = null;
            $userEmail = 'hamsftmo@gmail.com';

            try {
                $apiResponse = $this->hamExnessAuthService->getClientsData();

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

            // Apply filters (same as clients method)
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
                    $volumeLots = (float) ($client['volume_lots'] ?? 0);
                    $rewardUsd = (float) ($client['reward_usd'] ?? 0);
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

            // Calculate statistics
            $stats = [
                'total_accounts' => $clients->count(),
                'total_volume_lots' => $clients->sum('volume_lots'),
                'total_volume_usd' => $clients->sum('volume_mln_usd'),
                'total_profit' => $clients->sum('reward_usd'),
                'total_client_uids' => $clients->pluck('client_uid')->unique()->count()
            ];



            // Format client data with calculated fields
            $formattedClients = $clients->map(function ($client) {
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
                    'ftt_made' => ($volumeLots > 0)
                ];
            });

            Log::info('Ham client account data formatted successfully', [
                'count' => $formattedClients->count(),
                'data_source' => $dataSource
            ]);

            // Convert to paginated collection
            $perPage = 15; // จำนวนรายการต่อหน้าเป็น 15 ตามที่ต้องการ
            $currentPage = $request->get('page', 1);
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

            // แสดงปุ่มตัวเลขหน้าแบบฉลาด
            $maxButtons = 5; // จำนวนปุ่มตัวเลขที่จะแสดง
            $start = max(1, min($currentPage - floor($maxButtons / 2), $totalPages - $maxButtons + 1));
            $end = min($start + $maxButtons - 1, $totalPages);

            // เพิ่มปุ่มหน้าแรกถ้าไม่ได้อยู่ที่จุดเริ่มต้น
            if ($start > 1) {
                $pagination['links'][] = [
                    'url' => $request->fullUrlWithQuery(['page' => 1]),
                    'label' => '1',
                    'active' => false
                ];
                if ($start > 2) {
                    $pagination['links'][] = [
                        'url' => null,
                        'label' => '...',
                        'active' => false
                    ];
                }
            }

            // เพิ่มปุ่มตัวเลขหน้า
            for ($i = $start; $i <= $end; $i++) {
                $pagination['links'][] = [
                    'url' => $request->fullUrlWithQuery(['page' => $i]),
                    'label' => (string) $i,
                    'active' => $i == $currentPage
                ];
            }

            // เพิ่มปุ่มหน้าสุดท้ายถ้าไม่ได้อยู่ที่จุดสิ้นสุด
            if ($end < $totalPages) {
                if ($end < $totalPages - 1) {
                    $pagination['links'][] = [
                        'url' => null,
                        'label' => '...',
                        'active' => false
                    ];
                }
                $pagination['links'][] = [
                    'url' => $request->fullUrlWithQuery(['page' => $totalPages]),
                    'label' => (string) $totalPages,
                    'active' => false
                ];
            }

            $pagination['links'][] = ['url' => $pagination['next_page_url'], 'label' => 'Next &raquo;', 'active' => false];

            return Inertia::render('Admin/Report1/ClientAccount1', [
                'clients' => $pagination,
                'stats' => $stats,
                'filters' => $filters,
                'data_source' => $dataSource,
                'user_email' => $userEmail,
                'error' => $apiError
            ]);

        } catch (\Exception $e) {
            Log::error('Error in Report1Controller@clientAccount1: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return Inertia::render('Admin/Report1/ClientAccount1', [
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
                'user_email' => 'hamsftmo@gmail.com',
                'error' => 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage()
            ]);
        }
    }

    public function clientAccount1Api(Request $request)
    {
        try {
            Log::info('Fetching Ham client account data for API...');

            // Try to get data from Exness API first, fallback to database
            $dataSource = 'Database';
            $apiError = null;
            $userEmail = 'hamsftmo@gmail.com';

            try {
                $apiResponse = $this->hamExnessAuthService->getClientsData();

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

            // Format client data with calculated fields
            $formattedClients = $clients->map(function ($client) {
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
                    'ftt_made' => ($volumeLots > 0)
                ];
            });

            Log::info('Ham client account data formatted successfully for API', [
                'count' => $formattedClients->count(),
                'data_source' => $dataSource
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'clients' => $formattedClients->values(),
                    'stats' => [
                        'total_accounts' => $formattedClients->count(),
                        'total_volume_lots' => $formattedClients->sum('volume_lots'),
                        'total_volume_usd' => $formattedClients->sum('volume_mln_usd'),
                        'total_profit' => $formattedClients->sum('reward_usd'),
                        'total_client_uids' => $formattedClients->pluck('client_uid')->unique()->count()
                    ],
                    'data_source' => $dataSource,
                    'user_email' => $userEmail,
                    'error' => $apiError
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error in Report1Controller@clientAccount1Api: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'data' => [
                    'clients' => [],
                    'stats' => [
                        'total_accounts' => 0,
                        'total_volume_lots' => 0,
                        'total_volume_usd' => 0,
                        'total_profit' => 0,
                        'total_client_uids' => 0
                    ],
                    'data_source' => 'Error',
                    'user_email' => 'hamsftmo@gmail.com',
                    'error' => 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    public function clientTransaction1(Request $request)
    {
        try {
            $result = $this->hamExnessAuthService->getClientsData();

            if (isset($result['error'])) {
                Log::warning('Ham API failed for client transactions, using empty data', ['error' => $result['error']]);
                $clients = [];
            } else {
                $clients = $result['data'];
            }

            // Format transaction data
            $transactions = collect($clients)->map(function ($client) {
                return [
                    'client_uid' => $client['client_uid'] ?? '-',
                    'partner_account' => $client['partner_account'] ?? '-',
                    'transaction_type' => 'Trading',
                    'amount' => (float) ($client['volume_mln_usd'] ?? 0),
                    'currency' => 'USD',
                    'status' => 'Completed',
                    'created_at' => $client['reg_date'] ?? now(),
                    'volume_lots' => (float) ($client['volume_lots'] ?? 0),
                    'reward_usd' => (float) ($client['reward_usd'] ?? 0)
                ];
            })->toArray();

            return Inertia::render('Admin/Report1/ClientTransaction1', [
                'transactions' => $transactions,
                'filters' => $request->only(['client_uid', 'partner_account', 'status', 'date_range']),
                'dataSource' => 'Exness API',
                'userEmail' => 'hamsftmo@gmail.com',
                'total' => count($transactions)
            ]);

        } catch (\Exception $e) {
            Log::error('Report1 client transactions error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Inertia::render('Admin/Report1/ClientTransaction1', [
                'transactions' => [],
                'filters' => [],
                'dataSource' => 'Error',
                'userEmail' => 'hamsftmo@gmail.com',
                'error' => 'เกิดข้อผิดพลาดในการโหลดข้อมูล',
                'total' => 0
            ]);
        }
    }

    public function transactionsPending1(Request $request)
    {
        try {
            $result = $this->hamExnessAuthService->getClientsData();

            $pendingTransactions = collect($result['data'] ?? [])->filter(function ($client) {
                return (float) ($client['volume_lots'] ?? 0) > 0 && (float) ($client['reward_usd'] ?? 0) == 0;
            })->map(function ($client) {
                return [
                    'client_uid' => $client['client_uid'] ?? '-',
                    'partner_account' => $client['partner_account'] ?? '-',
                    'transaction_type' => 'Reward Pending',
                    'amount' => (float) ($client['volume_mln_usd'] ?? 0),
                    'volume_lots' => (float) ($client['volume_lots'] ?? 0),
                    'status' => 'Pending',
                    'created_at' => $client['reg_date'] ?? now()
                ];
            })->values()->toArray();

            return Inertia::render('Admin/Report1/TransactionsPending1', [
                'transactions' => $pendingTransactions,
                'filters' => $request->only(['client_uid', 'status', 'date_range']),
                'dataSource' => 'Exness API',
                'userEmail' => 'hamsftmo@gmail.com',
                'total' => count($pendingTransactions)
            ]);

        } catch (\Exception $e) {
            Log::error('Report1 pending transactions error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Inertia::render('Admin/Report1/TransactionsPending1', [
                'transactions' => [],
                'filters' => [],
                'dataSource' => 'Error',
                'userEmail' => 'hamsftmo@gmail.com',
                'error' => 'เกิดข้อผิดพลาดในการโหลดข้อมูล',
                'total' => 0
            ]);
        }
    }

    public function rewardHistory1(Request $request)
    {
        try {
            $result = $this->hamExnessAuthService->getClientsData();

            $rewardHistory = collect($result['data'] ?? [])->filter(function ($client) {
                return (float) ($client['reward_usd'] ?? 0) > 0;
            })->map(function ($client) {
                return [
                    'client_uid' => $client['client_uid'] ?? '-',
                    'partner_account' => $client['partner_account'] ?? '-',
                    'reward_amount' => (float) ($client['reward_usd'] ?? 0),
                    'volume_lots' => (float) ($client['volume_lots'] ?? 0),
                    'volume_usd' => (float) ($client['volume_mln_usd'] ?? 0),
                    'reward_date' => $client['reg_date'] ?? now(),
                    'status' => 'Paid'
                ];
            })->values()->toArray();

            return Inertia::render('Admin/Report1/RewardHistory1', [
                'rewards' => $rewardHistory,
                'filters' => $request->only(['client_uid', 'date_range']),
                'dataSource' => 'Exness API',
                'userEmail' => 'hamsftmo@gmail.com',
                'total' => count($rewardHistory),
                'totalReward' => collect($rewardHistory)->sum('reward_amount')
            ]);

        } catch (\Exception $e) {
            Log::error('Report1 reward history error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Inertia::render('Admin/Report1/RewardHistory1', [
                'rewards' => [],
                'filters' => [],
                'dataSource' => 'Error',
                'userEmail' => 'hamsftmo@gmail.com',
                'error' => 'เกิดข้อผิดพลาดในการโหลดข้อมูล',
                'total' => 0,
                'totalReward' => 0
            ]);
        }
    }

    public function testConnection2()
    {
        try {
            $data = $this->hamExnessAuthService->getClientsData();

            if (isset($data['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => $data['error'],
                    'account' => 'hamsftmo@gmail.com'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'การเชื่อมต่อสำเร็จ',
                'account' => 'hamsftmo@gmail.com',
                'data_count' => count($data['data'] ?? []),
                'sample_data' => $data['data'][0] ?? null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'account' => 'hamsftmo@gmail.com'
            ], 500);
        }
    }

    // Helper methods
    private function formatClientsData($data)
    {
        return collect($data)->map(function ($client) {
            $volumeLots = (float) ($client['volume_lots'] ?? 0);
            $rewardUsd = (float) ($client['reward_usd'] ?? 0);
            $clientStatus = ($volumeLots > 0 || $rewardUsd > 0) ? 'ACTIVE' : 'INACTIVE';

            return [
                'client_uid' => $client['client_uid'] ?? '-',
                'client_status' => $clientStatus,
                'reward_usd' => $rewardUsd,
                'rebate_amount_usd' => (float) ($client['rebate_amount_usd'] ?? 0),
                'volume_lots' => $volumeLots,
                'volume_mln_usd' => (float) ($client['volume_mln_usd'] ?? 0),
                'reg_date' => $client['reg_date'] ?? null,
                'partner_account' => $client['partner_account'] ?? '-',
                'client_country' => $client['client_country'] ?? $client['country'] ?? '-'
            ];
        })->toArray();
    }

    private function formatClientAccountData($data)
    {
        return collect($data)->map(function ($client) {
            $volumeLots = (float) ($client['volume_lots'] ?? 0);
            $rewardUsd = (float) ($client['reward_usd'] ?? 0);
            $clientStatus = ($volumeLots > 0 || $rewardUsd > 0) ? 'ACTIVE' : 'INACTIVE';
            $kycPassed = ($volumeLots > 1.0 || $rewardUsd > 10.0) ? true : null;

            return [
                'partner_account' => $client['partner_account'] ?? '-',
                'client_uid' => $client['client_uid'] ?? '-',
                'reg_date' => $client['reg_date'] ?? null,
                'client_country' => $client['client_country'] ?? $client['country'] ?? '-',
                'volume_lots' => $volumeLots,
                'volume_mln_usd' => (float) ($client['volume_mln_usd'] ?? 0),
                'reward_usd' => $rewardUsd,
                'client_status' => $clientStatus,
                'kyc_passed' => $kycPassed,
                'ftd_received' => ($volumeLots > 0 || $rewardUsd > 0),
                'ftt_made' => ($volumeLots > 0)
            ];
        })->toArray();
    }

    private function applyFilters($clients, $request)
    {
        $filtered = collect($clients);

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $filtered = $filtered->filter(function ($client) use ($search) {
                return str_contains(strtolower($client['client_uid']), $search) ||
                    str_contains(strtolower($client['partner_account']), $search);
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $filtered = $filtered->where('client_status', $request->status);
        }

        if ($request->filled('country')) {
            $filtered = $filtered->where('client_country', $request->country);
        }

        return $filtered->values()->toArray();
    }

    private function applyClientAccountFilters($clients, $request)
    {
        $filtered = collect($clients);

        if ($request->filled('partner_account')) {
            $filtered = $filtered->where('partner_account', $request->partner_account);
        }

        if ($request->filled('client_uid')) {
            $search = strtolower($request->client_uid);
            $filtered = $filtered->filter(function ($client) use ($search) {
                return str_contains(strtolower($client['client_uid']), $search);
            });
        }

        if ($request->filled('client_status') && $request->client_status !== '') {
            $filtered = $filtered->where('client_status', $request->client_status);
        }

        return $filtered->values()->toArray();
    }

    private function calculateStats($clients)
    {
        $total = count($clients);
        $active = collect($clients)->where('client_status', 'ACTIVE')->count();
        $inactive = $total - $active;

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive
        ];
    }
}