<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\KantapongExnessAuthService;
use App\Models\Client;
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
            // Get data from Exness API via KantapongExnessAuthService
            $apiResult = $this->kantapongExnessService->getClientsData();
            
            if (isset($apiResult['error'])) {
                Log::warning('Kantapong API failed, using database fallback', ['error' => $apiResult['error']]);
                
                // Fallback to database data
                $clients = Client::all()->map(function ($client) {
                    return [
                        'id' => $client->id,
                        'client_uid' => $client->client_uid,
                        'partner_account' => $client->partner_account ?? '',
                        'client_country' => $client->country ?? '',
                        'reg_date' => $client->reg_date ?? '',
                        'volume_lots' => (float) ($client->volume_lots ?? 0),
                        'volume_mln_usd' => (float) ($client->volume_mln_usd ?? 0),
                        'reward_usd' => (float) ($client->reward_usd ?? 0),
                        'client_status' => $client->client_status ?? 'UNKNOWN',
                        'kyc_passed' => $client->kyc_passed ?? false,
                        'ftd_received' => $client->ftd_received ?? false,
                        'ftt_made' => $client->ftt_made ?? false,
                    ];
                })->toArray();
                
                $dataSource = 'database';
            } else {
                $clients = $apiResult['data'];
                $dataSource = 'exness_api';
                Log::info('Kantapong Using Exness API data', ['count' => count($clients)]);
            }

            // Apply filters if provided
            if ($request->filled('search')) {
                $search = strtolower($request->search);
                $clients = array_filter($clients, function ($client) use ($search) {
                    return strpos(strtolower($client['client_uid'] ?? ''), $search) !== false ||
                           strpos(strtolower($client['client_country'] ?? ''), $search) !== false ||
                           strpos(strtolower($client['partner_account'] ?? ''), $search) !== false;
                });
            }

            if ($request->filled('country')) {
                $clients = array_filter($clients, function ($client) use ($request) {
                    return ($client['client_country'] ?? '') === $request->country;
                });
            }

            if ($request->filled('status')) {
                $clients = array_filter($clients, function ($client) use ($request) {
                    return ($client['client_status'] ?? '') === $request->status;
                });
            }

            // Calculate statistics from filtered data
            $totalClients = count($clients);
            $uniqueClients = count(array_unique(array_column($clients, 'client_uid')));
            $totalVolumeLots = array_sum(array_column($clients, 'volume_lots'));
            $totalVolumeUsd = array_sum(array_column($clients, 'volume_mln_usd'));
            $totalRewardUsd = array_sum(array_column($clients, 'reward_usd'));

            // Get unique countries for filter
            $countries = array_unique(array_column($clients, 'client_country'));
            sort($countries);

            // Get unique statuses for filter
            $statuses = array_unique(array_column($clients, 'client_status'));
            sort($statuses);

            // Reset array keys after filtering
            $clients = array_values($clients);

            return Inertia::render('Admin/Report2/Clients2', [
                'clients' => $clients,
                'stats' => [
                    'total_clients' => $totalClients,
                    'unique_clients' => $uniqueClients,
                    'total_volume_lots' => $totalVolumeLots,
                    'total_volume_usd' => $totalVolumeUsd,
                    'total_reward_usd' => $totalRewardUsd,
                    'data_source' => $dataSource,
                ],
                'filters' => [
                    'countries' => $countries,
                    'statuses' => $statuses,
                ],
                'currentFilters' => [
                    'search' => $request->search ?? '',
                    'country' => $request->country ?? '',
                    'status' => $request->status ?? '',
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Kantapong Report2 clients error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Inertia::render('Admin/Report2/Clients2', [
                'clients' => [],
                'stats' => [
                    'total_clients' => 0,
                    'unique_clients' => 0,
                    'total_volume_lots' => 0,
                    'total_volume_usd' => 0,
                    'total_reward_usd' => 0,
                    'data_source' => 'error',
                ],
                'filters' => [
                    'countries' => [],
                    'statuses' => [],
                ],
                'currentFilters' => [
                    'search' => '',
                    'country' => '',
                    'status' => '',
                ],
                'error' => 'เกิดข้อผิดพลาดในการโหลดข้อมูล: ' . $e->getMessage()
            ]);
        }
    }

    public function clientAccount2(Request $request)
    {
        try {
            // Get data from Exness API
            $apiResult = $this->kantapongExnessService->getClientsData();
            
            if (isset($apiResult['error'])) {
                Log::warning('Kantapong API failed for client accounts, using database fallback', ['error' => $apiResult['error']]);
                
                // Fallback to database
                $clients = Client::all()->map(function ($client) {
                    return [
                        'id' => $client->id,
                        'client_uid' => $client->client_uid,
                        'partner_account' => $client->partner_account ?? '',
                        'client_country' => $client->country ?? '',
                        'reg_date' => $client->reg_date ?? '',
                        'volume_lots' => (float) ($client->volume_lots ?? 0),
                        'volume_mln_usd' => (float) ($client->volume_mln_usd ?? 0),
                        'reward_usd' => (float) ($client->reward_usd ?? 0),
                        'client_status' => $client->client_status ?? 'UNKNOWN',
                    ];
                })->toArray();
                
                $dataSource = 'database';
            } else {
                $clients = $apiResult['data'];
                $dataSource = 'exness_api';
            }

            // Apply search filter
            if ($request->filled('search')) {
                $search = strtolower($request->search);
                $clients = array_filter($clients, function ($client) use ($search) {
                    return strpos(strtolower($client['client_uid'] ?? ''), $search) !== false ||
                           strpos(strtolower($client['partner_account'] ?? ''), $search) !== false;
                });
            }

            // Calculate statistics
            $totalAccounts = count($clients);
            $uniqueClients = count(array_unique(array_column($clients, 'client_uid')));
            $totalVolumeLots = array_sum(array_column($clients, 'volume_lots'));
            $totalVolumeUsd = array_sum(array_column($clients, 'volume_mln_usd'));
            $totalRewardUsd = array_sum(array_column($clients, 'reward_usd'));

            // Reset array keys
            $clients = array_values($clients);

            return Inertia::render('Admin/Report2/ClientAccount2', [
                'accounts' => $clients,
                'stats' => [
                    'total_accounts' => $totalAccounts,
                    'unique_clients' => $uniqueClients,
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
            Log::error('Kantapong Report2 client accounts error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Inertia::render('Admin/Report2/ClientAccount2', [
                'accounts' => [],
                'stats' => [
                    'total_accounts' => 0,
                    'unique_clients' => 0,
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