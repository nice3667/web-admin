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
            Log::info('Fetching Kantapong clients data for reports2/clients2...');

            // Try to get data from Exness API first, fallback to database
            $dataSource = 'Database';
            $apiError = null;
            $userEmail = 'kantapong.exness@gmail.com';

            try {
                $apiResponse = $this->kantapongExnessService->getClientsData();

                if (isset($apiResponse['error'])) {
                    throw new \Exception($apiResponse['error']);
                }

                $apiClients = $apiResponse['data'] ?? [];
                $dataSource = 'Exness API';

                Log::info('Successfully fetched data from Exness API', [
                    'count' => count($apiClients),
                    'user' => 'kantapong.exness@gmail.com'
                ]);

                // Use API data
                $clients = collect($apiClients);

            } catch (\Exception $e) {
                Log::warning('Failed to fetch from Exness API, using database data', [
                    'error' => $e->getMessage(),
                    'user' => 'kantapong.exness@gmail.com'
                ]);

                $apiError = $e->getMessage();

                // Fallback to database
                $query = KantapongClient::query();
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

            // Convert to paginated collection
            $perPage = 10;
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
                'data_source' => 'Error',
                'user_email' => 'kantapong.exness@gmail.com',
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
            $userEmail = 'kantapong.exness@gmail.com';

            try {
                $apiResponse = $this->kantapongExnessService->getClientsData();

                if (isset($apiResponse['error'])) {
                    throw new \Exception($apiResponse['error']);
                }

                $apiClients = $apiResponse['data'] ?? [];
                $dataSource = 'Exness API';

                Log::info('Successfully fetched data from Exness API', [
                    'count' => count($apiClients),
                    'user' => 'kantapong.exness@gmail.com'
                ]);

                // Use API data
                $clients = collect($apiClients);

            } catch (\Exception $e) {
                Log::warning('Failed to fetch from Exness API, using database data', [
                    'error' => $e->getMessage(),
                    'user' => 'kantapong.exness@gmail.com'
                ]);

                $apiError = $e->getMessage();

                // Fallback to database
                $query = KantapongClient::query();
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

            // Convert to paginated collection
            $perPage = 10;
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
                'user_email' => 'kantapong.exness@gmail.com',
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
            $result = $this->kantapongExnessService->testConnection();

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ]);
        }
    }
}