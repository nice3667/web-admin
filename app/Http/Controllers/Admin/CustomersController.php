<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HamClient;
use App\Models\KantapongClient;
use App\Models\JanischaClient;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CustomersController extends Controller
{
    public function index(Request $request)
    {
        try {
            Log::info('Fetching customers data for search...');

            // Get data from all three client tables
            $hamClients = $this->getClientsData(HamClient::class, $request);
            $kantapongClients = $this->getClientsData(KantapongClient::class, $request);
            $janischaClients = $this->getClientsData(JanischaClient::class, $request);

            // Combine all clients
            $allClients = $hamClients->concat($kantapongClients)->concat($janischaClients);

            // Apply search filters
            $filters = [];
            $filteredClients = $allClients;

            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $filteredClients = $filteredClients->filter(function($client) use ($searchTerm) {
                    return stripos($client['client_uid'], $searchTerm) !== false ||
                           stripos($client['client_id'], $searchTerm) !== false ||
                           stripos($client['partner_account'], $searchTerm) !== false;
                });
                $filters['search'] = $searchTerm;
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
                'total_volume_lots' => $filteredClients->sum('total_volume_lots'),
                'total_volume_usd' => $filteredClients->sum('total_volume_mln_usd'),
                'total_reward_usd' => $filteredClients->sum('total_reward_usd'),
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

            return Inertia::render('Admin/Customers/Index', [
                'customers' => $filteredClients->values(),
                'stats' => $stats,
                'users' => $users,
                'filters' => $filters
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

    private function getClientsData($modelClass, Request $request)
    {
        $query = $modelClass::query();

        // Apply search filters
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('client_uid', 'like', '%' . $searchTerm . '%')
                  ->orWhere('partner_account', 'like', '%' . $searchTerm . '%')
                  ->orWhere('client_id', 'like', '%' . $searchTerm . '%');
            });
        }

        if ($request->filled('client_status')) {
            $query->where('client_status', $request->client_status);
        }

        // Use pagination at database level for better performance
        $perPage = 50; // Increased from no pagination for better UX
        $clients = $query
            ->selectRaw('
                client_uid,
                MAX(partner_account) as partner_account,
                MAX(client_id) as client_id,
                MAX(reg_date) as reg_date,
                MAX(client_country) as client_country,
                SUM(volume_lots) as total_volume_lots,
                SUM(volume_mln_usd) as total_volume_mln_usd,
                SUM(reward_usd) as total_reward_usd,
                MAX(client_status) as client_status,
                MAX(kyc_passed) as kyc_passed,
                MAX(ftd_received) as ftd_received,
                MAX(ftt_made) as ftt_made,
                MAX(last_sync_at) as last_sync_at
            ')
            ->groupBy('client_uid')
            ->orderBy('reg_date', 'desc')
            ->paginate($perPage);

        // Format client data and add owner information
        $clients->getCollection()->transform(function ($client) {
            // Determine status from activity
            $volumeLots = (float)($client->total_volume_lots ?? 0);
            $rewardUsd = (float)($client->total_reward_usd ?? 0);
            $clientStatus = ($volumeLots > 0 || $rewardUsd > 0) ? 'ACTIVE' : 'INACTIVE';

            // KYC estimation based on activity level
            $kycPassed = ($volumeLots > 1.0 || $rewardUsd > 10.0) ? true : null;

            return [
                'client_uid' => $client->client_uid ?? '-',
                'partner_account' => $client->partner_account ?? '-',
                'client_id' => $client->client_id ?? '-',
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

        return $clients;
    }

    private function getOwnerInfo($clientUid)
    {
        $owners = [
            'hamsftmo@gmail.com' => 'Ham',
            'kantapong0592@gmail.com' => 'Kantapong',
            'Janischa.trade@gmail.com' => 'Janischa'
        ];

        return $owners[$clientUid] ?? 'Unknown';
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
            $updated += KantapongClient::where('client_uid', $request->client_uid)->update(['user_id' => $request->user_id]);
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
            // Search in all three tables
            $client = HamClient::where('client_uid', $clientUid)->first();
            $owner = 'Ham';

            if (!$client) {
                $client = KantapongClient::where('client_uid', $clientUid)->first();
                $owner = 'Kantapong';
            }

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
            // Get data from all three client tables
            $hamClients = $this->getClientsData(HamClient::class, $request);
            $kantapongClients = $this->getClientsData(KantapongClient::class, $request);
            $janischaClients = $this->getClientsData(JanischaClient::class, $request);

            // Combine all clients
            $allClients = $hamClients->concat($kantapongClients)->concat($janischaClients);

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
        $all = collect();

        // 1. Local DB clients
        $ham = HamClient::all();
        $kantapong = KantapongClient::all();
        $janischa = JanischaClient::all();
        $all = $all->concat($ham)->concat($kantapong)->concat($janischa);

        // 2. Exness/Janischa (ReportController)
        try {
            $exness = app(\App\Http\Controllers\Admin\ReportController::class)->clients($request);
            if (method_exists($exness, 'getData')) {
                $exnessData = $exness->getData();
                if (isset($exnessData['data']['clients'])) {
                    $all = $all->concat($exnessData['data']['clients']);
                }
            } elseif (is_a($exness, 'Illuminate\Http\JsonResponse')) {
                $data = $exness->getData(true);
                if (isset($data['data']['clients'])) {
                    $all = $all->concat($data['data']['clients']);
                }
            }
        } catch (\Throwable $e) {}

        // 3. Exness/Ham (Report1Controller)
        try {
            $exness1 = app(\App\Http\Controllers\Admin\Report1Controller::class)->clients1($request);
            if (method_exists($exness1, 'getData')) {
                $exnessData1 = $exness1->getData();
                if (isset($exnessData1['data']['clients'])) {
                    $all = $all->concat($exnessData1['data']['clients']);
                }
            } elseif (is_a($exness1, 'Illuminate\Http\JsonResponse')) {
                $data = $exness1->getData(true);
                if (isset($data['data']['clients'])) {
                    $all = $all->concat($data['data']['clients']);
                }
            }
        } catch (\Throwable $e) {}

        // 4. Exness/Kantapong (Report2Controller)
        try {
            $exness2 = app(\App\Http\Controllers\Admin\Report2Controller::class)->clients2($request);
            if (method_exists($exness2, 'getData')) {
                $exnessData2 = $exness2->getData();
                if (isset($exnessData2['data']['clients'])) {
                    $all = $all->concat($exnessData2['data']['clients']);
                }
            } elseif (is_a($exness2, 'Illuminate\Http\JsonResponse')) {
                $data = $exness2->getData(true);
                if (isset($data['data']['clients'])) {
                    $all = $all->concat($data['data']['clients']);
                }
            }
        } catch (\Throwable $e) {}

        // 5. XM (XMReportController)
        try {
            $xm = app(\App\Http\Controllers\Admin\XMReportController::class)->getTraderList($request);
            if (method_exists($xm, 'getData')) {
                $xmData = $xm->getData();
                if (is_array($xmData)) {
                    $all = $all->concat($xmData);
                }
            } elseif (is_a($xm, 'Illuminate\Http\JsonResponse')) {
                $data = $xm->getData(true);
                if (is_array($data)) {
                    $all = $all->concat($data);
                }
            }
        } catch (\Throwable $e) {}

        // Normalize fields for frontend search
        $normalized = $all->map(function ($c) {
            $arr = is_array($c) ? $c : (array)$c;
            return [
                'client_uid' => $arr['client_uid'] ?? $arr['clientId'] ?? $arr['traderId'] ?? $arr['account'] ?? $arr['login'] ?? null,
                'client_id' => $arr['client_id'] ?? $arr['clientId'] ?? $arr['traderId'] ?? $arr['account'] ?? $arr['login'] ?? null,
                'client_account' => $arr['client_account'] ?? $arr['account'] ?? $arr['login'] ?? $arr['traderId'] ?? null,
                'partner_account' => $arr['partner_account'] ?? $arr['campaign'] ?? null,
                'traderId' => $arr['traderId'] ?? null,
                'client_name' => $arr['client_name'] ?? $arr['name'] ?? null,
                'account_number' => $arr['account_number'] ?? $arr['account'] ?? $arr['login'] ?? null,
                'login' => $arr['login'] ?? null,
                'exness_id' => $arr['exness_id'] ?? null,
                'country' => $arr['client_country'] ?? $arr['country'] ?? null,
                'status' => $arr['client_status'] ?? $arr['status'] ?? $arr['valid'] ?? null,
                'reg_date' => $arr['reg_date'] ?? $arr['signUpDate'] ?? null,
                'reward_usd' => $arr['reward_usd'] ?? $arr['total_reward_usd'] ?? null,
                'rebate_amount_usd' => $arr['rebate_amount_usd'] ?? null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'customers' => $normalized,
            ]
        ]);
    }
}
