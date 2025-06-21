<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        try {
            Log::info('Fetching clients data...');
            
            $query = Client::query();

            // Apply filters
            $filters = [];
            
            if ($request->filled('partner_account')) {
                $query->where('partner_account', 'like', '%' . $request->partner_account . '%');
                $filters['partner_account'] = $request->partner_account;
            }

            if ($request->filled('client_uid')) {
                $query->where('client_uid', 'like', '%' . $request->client_uid . '%');
                $filters['client_uid'] = $request->client_uid;
            }

            if ($request->filled('client_country')) {
                $query->where('client_country', $request->client_country);
                $filters['client_country'] = $request->client_country;
            }

            if ($request->filled('client_status')) {
                $query->where('client_status', $request->client_status);
                $filters['client_status'] = $request->client_status;
            }

            if ($request->filled('reg_date')) {
                $query->whereDate('reg_date', $request->reg_date);
                $filters['reg_date'] = $request->reg_date;
            }

            if ($request->filled('kyc_passed')) {
                $query->where('kyc_passed', $request->kyc_passed);
                $filters['kyc_passed'] = $request->kyc_passed;
            }

            // Get all clients (no pagination)
            $clients = $query->get();

            Log::info('Clients query result:', ['count' => $clients->count()]);

            // Calculate statistics from the same filtered query (without pagination)
            $statsQuery = Client::query();
            
            // Apply the same filters to stats calculation
            if ($request->filled('partner_account')) {
                $statsQuery->where('partner_account', 'like', '%' . $request->partner_account . '%');
            }
            if ($request->filled('client_uid')) {
                $statsQuery->where('client_uid', 'like', '%' . $request->client_uid . '%');
            }
            if ($request->filled('client_country')) {
                $statsQuery->where('client_country', $request->client_country);
            }
            if ($request->filled('client_status')) {
                $statsQuery->where('client_status', $request->client_status);
            }
            if ($request->filled('reg_date')) {
                $statsQuery->whereDate('reg_date', $request->reg_date);
            }
            if ($request->filled('kyc_passed')) {
                $statsQuery->where('kyc_passed', $request->kyc_passed);
            }

            $stats = [
                'total_accounts' => $statsQuery->count(),
                'total_volume_lots' => $statsQuery->sum('volume_lots'),
                'total_volume_usd' => $statsQuery->sum('volume_mln_usd'),
                'total_profit' => $statsQuery->sum('reward_usd'),
                'total_client_uids' => $statsQuery->distinct('client_uid')->count()
            ];

            Log::info('Stats calculated:', $stats);

            // Format client data
            $formattedClients = $clients->map(function ($client) {
                // Determine status from activity (same logic as Report1 but using database data)
                $volumeLots = (float)($client->volume_lots ?? 0);
                $rewardUsd = (float)($client->reward_usd ?? 0);
                
                // Use activity-based status determination instead of old client_status
                $clientStatus = ($volumeLots > 0 || $rewardUsd > 0) ? 'ACTIVE' : 'INACTIVE';
                
                // KYC estimation based on activity level (same as Report1)
                $kycPassed = ($volumeLots > 1.0 || $rewardUsd > 10.0) ? true : null;
                
                return [
                    'partner_account' => $client->partner_account ?? '-',
                    'client_uid' => $client->client_uid ?? '-',
                    'reg_date' => $client->reg_date,
                    'client_country' => $client->client_country ?? '-',
                    'volume_lots' => $volumeLots,
                    'volume_mln_usd' => (float)($client->volume_mln_usd ?? 0),
                    'reward_usd' => $rewardUsd,
                    'client_status' => $clientStatus, // Use calculated status
                    'kyc_passed' => $kycPassed, // Use calculated KYC
                    'ftd_received' => ($volumeLots > 0 || $rewardUsd > 0), // Calculate from activity
                    'ftt_made' => ($volumeLots > 0) // Calculate from trading activity
                ];
            });

            Log::info('Data formatted successfully');

            if ($request->wantsJson()) {
                return response()->json([
                    'data' => [
                        'clients' => $formattedClients,
                        'stats' => $stats
                    ]
                ]);
            }

            return Inertia::render('Admin/Report/ClientAccount', [
                'clients' => $formattedClients,
                'stats' => $stats,
                'filters' => $filters
            ]);

        } catch (\Exception $e) {
            Log::error('Error in ClientController@index: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage()
                ], 500);
            }

            return Inertia::render('Admin/Report/ClientAccount', [
                'clients' => collect([]),
                'stats' => [
                    'total_accounts' => 0,
                    'total_volume_lots' => 0,
                    'total_volume_usd' => 0,
                    'total_profit' => 0,
                    'total_client_uids' => 0
                ],
                'filters' => $filters,
                'error' => 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage()
            ]);
        }
    }

    public function clients(Request $request)
    {
        try {
            Log::info('Fetching clients data for reports/clients...');
            
            $query = Client::query();

            // Apply filters
            $filters = [];
            
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

            Log::info('Clients query result:', ['count' => $clients->count()]);

            // Calculate statistics from the same filtered query
            $statsQuery = Client::query();
            
            // Apply the same filters to stats calculation
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

            // Get aggregated stats by client_uid to avoid double-counting
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

            Log::info('Stats calculated:', $stats);

            // Format client data for the clients view
            $formattedClients = $clients->map(function ($client) {
                // Determine status from activity (same logic as Report1 but using database data)
                $volumeLots = (float)($client->total_volume_lots ?? 0);
                $rewardUsd = (float)($client->total_reward_usd ?? 0);
                
                // Use activity-based status determination instead of old client_status
                $clientStatus = ($volumeLots > 0 || $rewardUsd > 0) ? 'ACTIVE' : 'INACTIVE';
                
                return [
                    'client_uid' => $client->client_uid ?? '-',
                    'client_status' => $clientStatus, // Use calculated status instead of database status
                    'reward_usd' => $rewardUsd,
                    'rebate_amount_usd' => (float)($client->total_rebate_amount_usd ?? 0),
                    'volume_lots' => $volumeLots,
                    'volume_mln_usd' => (float)($client->total_volume_mln_usd ?? 0),
                    'reg_date' => $client->reg_date,
                    'partner_account' => $client->partner_account ?? '-',
                    'client_country' => $client->client_country ?? '-'
                ];
            });

            Log::info('Data formatted successfully');

            if ($request->wantsJson()) {
                return response()->json([
                    'data' => [
                        'clients' => $formattedClients,
                        'stats' => $stats
                    ]
                ]);
            }

            return Inertia::render('Admin/Report/Clients', [
                'clients' => $formattedClients,
                'stats' => $stats,
                'filters' => $filters
            ]);

        } catch (\Exception $e) {
            Log::error('Error in ClientController@clients: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage()
                ], 500);
            }

            return Inertia::render('Admin/Report/Clients', [
                'clients' => collect([]),
                'stats' => [
                    'total_pending' => 0,
                    'total_amount' => 0,
                    'due_today' => 0,
                    'overdue' => 0,
                    'total_client_uids' => 0
                ],
                'filters' => $filters,
                'error' => 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage()
            ]);
        }
    }
} 