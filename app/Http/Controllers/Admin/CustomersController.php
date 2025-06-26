<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class CustomersController extends Controller
{
    public function index(Request $request)
    {
        try {
            Log::info('Fetching customers data for search...');

            $query = Client::query();

            // Apply search filters
            $filters = [];

            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('client_uid', 'like', '%' . $searchTerm . '%')
                      ->orWhere('partner_account', 'like', '%' . $searchTerm . '%')
                      ->orWhere('client_id', 'like', '%' . $searchTerm . '%')
                      ->orWhere('client_country', 'like', '%' . $searchTerm . '%');
                });
                $filters['search'] = $searchTerm;
            }

            if ($request->filled('client_uid')) {
                $query->where('client_uid', 'like', '%' . $request->client_uid . '%');
                $filters['client_uid'] = $request->client_uid;
            }

            if ($request->filled('partner_account')) {
                $query->where('partner_account', 'like', '%' . $request->partner_account . '%');
                $filters['partner_account'] = $request->partner_account;
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

            // Get unique clients grouped by client_uid with user relationship
            $clients = $query
                ->with('user:id,name,email,username')
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
                    MAX(user_id) as user_id
                ')
                ->groupBy('client_uid')
                ->orderBy('reg_date', 'desc')
                ->get();

            Log::info('Customers query result:', ['count' => $clients->count()]);

            // Get all users for owner assignment
            $users = User::select('id', 'name', 'email', 'username')
                ->whereHas('roles', function($q) {
                    $q->whereIn('name', ['admin', 'super-admin', 'user']);
                })
                ->get();

            // Calculate statistics
            $stats = [
                'total_customers' => $clients->count(),
                'total_volume_lots' => $clients->sum('total_volume_lots'),
                'total_volume_usd' => $clients->sum('total_volume_mln_usd'),
                'total_reward_usd' => $clients->sum('total_reward_usd'),
                'total_rebate_usd' => $clients->sum('total_rebate_amount_usd'),
                'active_customers' => $clients->where('client_status', 'ACTIVE')->count(),
                'inactive_customers' => $clients->where('client_status', 'INACTIVE')->count(),
            ];

            // Format client data
            $formattedClients = $clients->map(function ($client) {
                // Calculate status based on activity
                $volumeLots = (float)($client->total_volume_lots ?? 0);
                $rewardUsd = (float)($client->total_reward_usd ?? 0);

                $calculatedStatus = ($volumeLots > 0 || $rewardUsd > 0) ? 'ACTIVE' : 'INACTIVE';

                return [
                    'client_uid' => $client->client_uid ?? '-',
                    'partner_account' => $client->partner_account ?? '-',
                    'client_id' => $client->client_id ?? '-',
                    'reg_date' => $client->reg_date,
                    'client_country' => $client->client_country ?? '-',
                    'total_volume_lots' => $volumeLots,
                    'total_volume_mln_usd' => (float)($client->total_volume_mln_usd ?? 0),
                    'total_reward_usd' => $rewardUsd,
                    'total_rebate_amount_usd' => (float)($client->total_rebate_amount_usd ?? 0),
                    'client_status' => $calculatedStatus,
                    'kyc_passed' => (bool)($client->kyc_passed),
                    'ftd_received' => (bool)($client->ftd_received),
                    'ftt_made' => (bool)($client->ftt_made),
                    'last_sync_at' => $client->last_sync_at,
                    'owner' => $client->user ? [
                        'id' => $client->user->id,
                        'name' => $client->user->name,
                        'email' => $client->user->email,
                        'username' => $client->user->username
                    ] : null,
                ];
            });

            Log::info('Data formatted successfully');

            if ($request->wantsJson()) {
                return response()->json([
                    'data' => [
                        'customers' => $formattedClients,
                        'stats' => $stats,
                        'users' => $users
                    ]
                ]);
            }

            return Inertia::render('Admin/Customers/Index', [
                'customers' => $formattedClients,
                'stats' => $stats,
                'users' => $users,
                'filters' => $filters
            ]);

        } catch (\Exception $e) {
            Log::error('Error in CustomersController@index: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage()
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
                'filters' => $filters,
                'error' => 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage()
            ]);
        }
    }

    public function assignOwner(Request $request)
    {
        try {
            $request->validate([
                'client_uid' => 'required|string',
                'user_id' => 'required|exists:users,id'
            ]);

            // Update client records with user_id
            $updated = Client::where('client_uid', $request->client_uid)
                ->update(['user_id' => $request->user_id]);

            if ($updated) {
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
            $client = Client::with('user:id,name,email,username')
                ->where('client_uid', $clientUid)
                ->selectRaw('
                    client_uid,
                    partner_account,
                    client_id,
                    reg_date,
                    client_country,
                    volume_lots,
                    volume_mln_usd,
                    reward_usd,
                    rebate_amount_usd,
                    client_status,
                    kyc_passed,
                    ftd_received,
                    ftt_made,
                    last_sync_at,
                    user_id
                ')
                ->first();

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
                    'owner' => $client->user
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
}
