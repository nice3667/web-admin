<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Services\ExnessSyncService;
use App\Models\ExnessClient;
use App\Models\ExnessUser;
use App\Models\User;
use Inertia\Inertia;
use App\Services\ExnessAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;

class ExnessController extends Controller
{
    private ExnessSyncService $exnessSyncService;
    protected ExnessAuthService $exnessService;

    public function __construct(ExnessSyncService $exnessSyncService, ExnessAuthService $exnessService)
    {
        $this->exnessSyncService = $exnessSyncService;
        $this->exnessService = $exnessService;
    }

    public function credentials()
    {
        return Inertia::render('Admin/Exness/Credentials');
    }

    public function updateCredentials(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Try to sync with new credentials
        $user = Auth::user();
        $syncResult = $this->exnessSyncService->syncUserDataOnLogin(
            $user,
            $request->email,
            $request->password
        );

        if ($syncResult) {
            return redirect()->back()->with('success', 'Exness credentials updated and synced successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to verify Exness credentials.');
        }
    }

    /**
     * Get clients data from Exness API
     */
    public function getClients(Request $request)
    {
        try {
            $result = $this->exnessService->getClientsData();
            
            if (isset($result['error'])) {
                return response()->json(['error' => $result['error']], 400);
            }

            // Return the data directly without transformation
            return response()->json([
                'data' => $result['data'] ?? [],
                'debug' => [
                    'source' => 'api',
                    'timestamp' => now()->toISOString(),
                    'client_count' => count($result['data'] ?? [])
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting clients from Exness API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'เกิดข้อผิดพลาดในการดึงข้อมูลลูกค้า',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get wallet accounts data from database (for dashboard)
     */
    public function getWalletAccounts(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            Log::info('Getting wallet accounts from database for user', ['user_id' => $user->id]);

            // Get cached statistics
            $stats = $this->exnessSyncService->getCachedStats($user->id);
            
            if (!$stats || $stats['total_accounts'] == 0) {
                return response()->json([
                    'error' => 'ไม่พบข้อมูลบัญชี กรุณาตรวจสอบบัญชี Exness ของคุณ',
                                         'debug' => [
                         'source' => 'database',
                         'user_id' => $user->id,
                         'has_exness_user' => $user->exnessUser && $user->exnessUser->is_active
                     ]
                ], 401);
            }

            // Convert to format expected by frontend
            $combinedWallets = ExnessClient::forUser($user->id)->get()->map(function ($client) {
                return [
                    'source' => 'database',
                    'client_id' => $client->client_uid,
                    'client_name' => $client->client_name ?? 'Unknown',
                    'email' => $client->client_email ?? '',
                    'balance' => (float) $client->reward_usd,
                    'currency' => $client->currency ?? 'USD',
                                         'registration_date' => $client->reg_date?->format('Y-m-d'),
                    'last_activity' => $client->last_activity?->toISOString()
                ];
            })->toArray();

            Log::info('Wallet accounts retrieved from database', [
                'user_id' => $user->id,
                'total_wallets' => count($combinedWallets),
                'total_balance' => $stats['total_reward']
            ]);

            return response()->json([
                'combined_wallets' => $combinedWallets,
                'stats' => $stats,
                'debug' => [
                    'source' => 'database',
                    'cached' => true,
                    'timestamp' => now()->toISOString(),
                    'user_id' => $user->id,
                    'total_wallets' => count($combinedWallets)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting wallet accounts from database', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'เกิดข้อผิดพลาดในการดึงข้อมูลบัญชี',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manual sync trigger (for admin use)
     */
    public function syncData(Request $request)
    {
        try {
            $user = Auth::user();
            
                         if (!$user->exnessUser || !$user->exnessUser->is_active) {
                return response()->json([
                    'error' => 'ไม่พบข้อมูล Exness credentials สำหรับผู้ใช้นี้'
                ], 400);
            }

            $exnessUser = $user->exnessUser;
            $syncResult = $this->exnessSyncService->syncUserDataOnLogin(
                $user,
                $exnessUser->exness_email,
                $exnessUser->exness_password
            );

            if ($syncResult) {
                return response()->json([
                    'message' => 'ซิงค์ข้อมูลสำเร็จ',
                    'stats' => $this->exnessSyncService->getCachedStats($user->id)
                ]);
            } else {
                return response()->json([
                    'error' => 'ไม่สามารถซิงค์ข้อมูลได้'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error during manual sync', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'error' => 'เกิดข้อผิดพลาดในการซิงค์ข้อมูล',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Debug method to check raw API responses
     */
    public function debugApiResponses(Request $request)
    {
        try {
            $token = $this->exnessService->retrieveToken();
            if (!$token) {
                return response()->json(['error' => 'ไม่สามารถรับ token ได้'], 400);
            }

            // Get raw responses from both APIs
            $v1Response = $this->exnessService->getClientsFromUrl(
                "https://my.exnessaffiliates.com/api/reports/clients/",
                'v1'
            );
            
            $v2Response = $this->exnessService->getClientsFromUrl(
                "https://my.exnessaffiliates.com/api/v2/reports/clients/",
                'v2'
            );

            $v1Clients = $v1Response['data'] ?? [];
            $v2Clients = $v2Response['data'] ?? [];

            // Get client_uid lists from both APIs
            $v1Uids = array_column($v1Clients, 'client_uid');
            $v2Uids = array_column($v2Clients, 'client_uid');

            // Find matching and non-matching UIDs
            $matchingUids = array_intersect($v1Uids, $v2Uids);
            $v1OnlyUids = array_diff($v1Uids, $v2Uids);
            $v2OnlyUids = array_diff($v2Uids, $v1Uids);

            // Sample data from each API
            $v1Sample = $v1Clients[0] ?? null;
            $v2Sample = $v2Clients[0] ?? null;

            return response()->json([
                'v1_api' => [
                    'total_clients' => count($v1Clients),
                    'sample_client' => $v1Sample,
                    'available_fields' => $v1Sample ? array_keys($v1Sample) : [],
                    'client_uids' => array_slice($v1Uids, 0, 10) // First 10 UIDs
                ],
                'v2_api' => [
                    'total_clients' => count($v2Clients),
                    'sample_client' => $v2Sample,
                    'available_fields' => $v2Sample ? array_keys($v2Sample) : [],
                    'client_uids' => array_slice($v2Uids, 0, 10) // First 10 UIDs
                ],
                'matching_analysis' => [
                    'matching_uids_count' => count($matchingUids),
                    'v1_only_count' => count($v1OnlyUids),
                    'v2_only_count' => count($v2OnlyUids),
                    'matching_uids_sample' => array_slice($matchingUids, 0, 5),
                    'v1_only_sample' => array_slice($v1OnlyUids, 0, 5),
                    'v2_only_sample' => array_slice($v2OnlyUids, 0, 5)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'เกิดข้อผิดพลาดในการตรวจสอบ API',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Debug method to check database status
     */
    public function debugDatabaseStatus(Request $request)
    {
        try {
            // Check clients table
            $totalClients = \App\Models\Client::count();
            $statusCounts = \App\Models\Client::selectRaw('client_status, COUNT(*) as count')
                ->groupBy('client_status')
                ->get()
                ->pluck('count', 'client_status')
                ->toArray();

            // Sample clients with their status
            $sampleClients = \App\Models\Client::select('client_uid', 'client_status', 'last_sync_at')
                ->limit(10)
                ->get();

            // Check if rebate_amount_usd column exists
            $hasRebateColumn = Schema::hasColumn('clients', 'rebate_amount_usd');

            return response()->json([
                'database_status' => [
                    'total_clients' => $totalClients,
                    'status_distribution' => $statusCounts,
                    'has_rebate_column' => $hasRebateColumn,
                    'sample_clients' => $sampleClients
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'เกิดข้อผิดพลาดในการตรวจสอบฐานข้อมูล',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Legacy methods (kept for backward compatibility)
    public function getToken()
    {
        return response()->json(['message' => 'This endpoint is deprecated. Data is now cached in database.']);
    }

    public function getWallets()
    {
        return $this->getClients();
    }

    public function saveCredentials(Request $request)
    {
        return $this->updateCredentials($request);
    }
}
