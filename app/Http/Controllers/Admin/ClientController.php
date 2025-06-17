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
            if ($request->filled('partner_account')) {
                $query->where('partner_account', 'like', '%' . $request->partner_account . '%');
            }

            if ($request->filled('client_uid')) {
                $query->where('client_uid', 'like', '%' . $request->client_uid . '%');
            }

            if ($request->filled('client_country')) {
                $query->where('client_country', $request->client_country);
            }

            if ($request->filled('client_status')) {
                $query->where('client_status', $request->client_status);
            }

            if ($request->filled('reg_date')) {
                $query->whereDate('reg_date', $request->reg_date);
            }

            if ($request->filled('kyc_passed')) {
                $query->where('kyc_passed', $request->kyc_passed);
            }

            // Get paginated clients
            $clients = $query->paginate($request->input('per_page', 10));

            Log::info('Clients query result:', ['count' => $clients->count()]);

            // Calculate statistics
            $stats = [
                'total_accounts' => Client::count(),
                'total_volume_lots' => Client::sum('volume_lots'),
                'total_volume_usd' => Client::sum('volume_mln_usd'),
                'total_profit' => Client::sum('reward_usd')
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
                'stats' => $stats
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
                'error' => 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage()
            ]);
        }
    }
} 