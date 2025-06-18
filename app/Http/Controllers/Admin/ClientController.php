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

            // Get paginated clients
            $clients = $query->paginate($request->input('per_page', 10));

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
                'total_profit' => $statsQuery->sum('reward_usd')
            ];

            Log::info('Stats calculated:', $stats);

            // Format client data
            $formattedClients = $clients->through(function ($client) {
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
                    'total_profit' => 0
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

            // Get all clients (no pagination for this view)
            $clients = $query->orderBy('reg_date', 'desc')->get();

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

            $stats = [
                'total_pending' => $statsQuery->count(),
                'total_amount' => $statsQuery->sum('volume_lots'),
                'due_today' => $statsQuery->sum('volume_mln_usd'),
                'overdue' => $statsQuery->sum('reward_usd')
            ];

            Log::info('Stats calculated:', $stats);

            // Format client data for the clients view
            $formattedClients = $clients->map(function ($client) {
                return [
                    'client_uid' => $client->client_uid ?? '-',
                    'client_status' => $client->client_status ?? 'UNKNOWN',
                    'reward_usd' => (float)($client->reward_usd ?? 0),
                    'rebate_amount_usd' => (float)($client->rebate_amount_usd ?? 0),
                    'volume_lots' => (float)($client->volume_lots ?? 0),
                    'volume_mln_usd' => (float)($client->volume_mln_usd ?? 0),
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
                    'overdue' => 0
                ],
                'filters' => $filters,
                'error' => 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage()
            ]);
        }
    }
} 