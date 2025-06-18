<?php

namespace App\Http\Controllers;

use App\Services\ClientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;

class RealtimeSyncController extends Controller
{
    protected $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    /**
     * Start real-time sync monitoring
     */
    public function startMonitoring(Request $request): JsonResponse
    {
        try {
            $interval = $request->input('interval', 30); // minutes
            $newOnly = $request->input('new_only', true);
            
            // Store monitoring settings in cache
            Cache::put('realtime_sync_settings', [
                'interval' => $interval,
                'new_only' => $newOnly,
                'started_at' => now(),
                'is_active' => true
            ], now()->addDays(1));

            // Start background sync process
            $this->startBackgroundSync($interval, $newOnly);

            return response()->json([
                'success' => true,
                'message' => 'Real-time sync monitoring started',
                'data' => [
                    'interval' => $interval,
                    'new_only' => $newOnly,
                    'started_at' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error starting real-time sync monitoring', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to start monitoring: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Stop real-time sync monitoring
     */
    public function stopMonitoring(): JsonResponse
    {
        try {
            // Update monitoring settings
            $settings = Cache::get('realtime_sync_settings', []);
            $settings['is_active'] = false;
            $settings['stopped_at'] = now();
            
            Cache::put('realtime_sync_settings', $settings, now()->addDays(1));

            return response()->json([
                'success' => true,
                'message' => 'Real-time sync monitoring stopped',
                'data' => [
                    'stopped_at' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error stopping real-time sync monitoring', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to stop monitoring: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get monitoring status
     */
    public function getStatus(): JsonResponse
    {
        try {
            $settings = Cache::get('realtime_sync_settings', []);
            $lastSync = Cache::get('last_client_sync');
            $syncStats = $this->clientService->getSyncStats();

            return response()->json([
                'success' => true,
                'data' => [
                    'monitoring_active' => $settings['is_active'] ?? false,
                    'interval' => $settings['interval'] ?? null,
                    'new_only' => $settings['new_only'] ?? true,
                    'started_at' => $settings['started_at'] ?? null,
                    'stopped_at' => $settings['stopped_at'] ?? null,
                    'last_sync' => $lastSync,
                    'sync_stats' => $syncStats
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting monitoring status', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manual trigger sync
     */
    public function triggerSync(Request $request): JsonResponse
    {
        try {
            $newOnly = $request->input('new_only', true);
            
            if ($newOnly) {
                $result = $this->clientService->syncNewClients();
                $success = $result['success'] ?? false;
                
                if ($success) {
                    return response()->json([
                        'success' => true,
                        'message' => 'New clients sync triggered successfully',
                        'data' => $result
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'New clients sync failed: ' . ($result['error'] ?? 'Unknown error')
                    ], 500);
                }
            } else {
                $success = $this->clientService->syncClients();
                
                if ($success) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Full sync triggered successfully'
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Full sync failed'
                    ], 500);
                }
            }

        } catch (\Exception $e) {
            Log::error('Error triggering sync', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to trigger sync: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sync history
     */
    public function getSyncHistory(): JsonResponse
    {
        try {
            $history = Cache::get('sync_history', []);
            
            // Limit to last 50 entries
            $history = array_slice($history, -50);

            return response()->json([
                'success' => true,
                'data' => $history
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting sync history', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get sync history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Start background sync process
     */
    protected function startBackgroundSync($interval, $newOnly)
    {
        // This would typically be handled by a queue job
        // For now, we'll just log the intention
        Log::info('Background sync process started', [
            'interval' => $interval,
            'new_only' => $newOnly
        ]);

        // In a real implementation, you would:
        // 1. Create a queue job for the sync
        // 2. Schedule it to run at the specified interval
        // 3. Use Laravel's queue system or a cron job
    }

    /**
     * WebSocket endpoint for real-time updates
     */
    public function websocketEndpoint(Request $request)
    {
        // This would be handled by a WebSocket server
        // For now, we'll return a simple response
        return response()->json([
            'success' => true,
            'message' => 'WebSocket endpoint ready',
            'data' => [
                'connection_id' => uniqid(),
                'timestamp' => now()->toISOString()
            ]
        ]);
    }
} 