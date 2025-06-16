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

class ExnessController extends Controller
{
    private ExnessSyncService $exnessSyncService;

    public function __construct(ExnessSyncService $exnessSyncService)
    {
        $this->exnessSyncService = $exnessSyncService;
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
     * Get clients data from database (with cache)
     */
    public function getClients(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            Log::info('Getting clients from database for user', ['user_id' => $user->id]);

            // Check if user needs sync
            if ($this->exnessSyncService->userNeedsSync($user->id)) {
                Log::info('User data needs sync, attempting background sync', ['user_id' => $user->id]);
                
                // Try to get fresh data if user has valid credentials
                if ($user->exnessUser && $user->exnessUser->is_active) {
                    $exnessUser = $user->exnessUser;
                    $this->exnessSyncService->syncUserDataOnLogin(
                        $user,
                        $exnessUser->exness_email,
                        $exnessUser->exness_password
                    );
                }
            }

            // Get cached client data
            $clients = $this->exnessSyncService->getCachedClientData($user->id);
            
            if ($clients->isEmpty()) {
                Log::warning('No client data found for user', ['user_id' => $user->id]);
                return response()->json([
                    'data_v1' => [],
                    'data_v2' => [],
                    'message' => 'ไม่พบข้อมูลลูกค้า กรุณาตรวจสอบบัญชี Exness ของคุณ'
                ]);
            }

            // Convert to format expected by frontend
            $v1Data = $clients->map(function ($client) {
                return [
                    'client_uid' => $client->client_uid,
                    'client_name' => $client->client_name,
                    'client_email' => $client->client_email,
                    'volume_lots' => (float) $client->volume_lots,
                    'volume_mln_usd' => (float) $client->volume_mln_usd,
                    'reward_usd' => (float) $client->reward_usd,
                    'currency' => $client->currency,
                    'reg_date' => $client->reg_date?->format('Y-m-d'),
                    'last_activity' => $client->last_activity?->toISOString()
                ];
            })->toArray();

            $v2Data = $clients->map(function ($client) {
                return [
                    'client_uid' => $client->client_uid,
                    'client_status' => $client->client_status,
                    'rebate_amount_usd' => (float) $client->rebate_amount_usd
                ];
            })->toArray();

            Log::info('Client data retrieved from database', [
                'user_id' => $user->id,
                'client_count' => $clients->count()
            ]);

            return response()->json([
                'data_v1' => $v1Data,
                'data_v2' => $v2Data,
                'debug' => [
                    'source' => 'database',
                    'cached' => true,
                    'timestamp' => now()->toISOString(),
                    'user_id' => $user->id,
                    'client_count' => $clients->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting clients from database', [
                'user_id' => Auth::id(),
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
