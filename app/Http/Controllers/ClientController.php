<?php

namespace App\Http\Controllers;

use App\Services\ClientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ClientController extends Controller
{
    protected $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function index(Request $request)
    {
        try {
            // Sync data from Exness API first
            Log::info('Starting automatic data sync');
            $syncSuccess = $this->clientService->syncClients();
            
            if (!$syncSuccess) {
                Log::warning('Automatic sync failed, continuing with existing data');
            }

            $filters = $request->only([
                'partner_account',
                'client_country',
                'client_status',
                'kyc_passed',
                'start_date',
                'end_date'
            ]);

            $clients = $this->clientService->getClients($filters);
            $stats = $this->clientService->getClientStats();

            // Log the results for debugging
            Log::info('Client data retrieved:', [
                'clients_count' => count($clients),
                'stats' => $stats
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'clients' => $clients,
                    'stats' => $stats,
                    'sync_status' => $syncSuccess ? 'success' : 'failed'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error in client index:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูลลูกค้า: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sync(Request $request)
    {
        try {
            Log::info('Manual client sync triggered');
            
            $success = $this->clientService->syncClients();
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Client data synchronized successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to synchronize client data'
                ], 500);
            }
            
        } catch (\Exception $e) {
            Log::error('Error in manual sync:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error during synchronization: ' . $e->getMessage()
            ], 500);
        }
    }

    public function stats()
    {
        try {
            $stats = $this->clientService->getClientStats();
            Log::info('Client stats retrieved:', $stats);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching client stats:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการดึงสถิติลูกค้า: ' . $e->getMessage()
            ], 500);
        }
    }

    public function debugApi(Request $request)
    {
        try {
            $apiData = $this->clientService->getRawApiData();
            
            if (isset($apiData['error'])) {
                return response()->json(['error' => $apiData['error']], 400);
            }

            return response()->json($apiData);

        } catch (\Exception $e) {
            Log::error('Error in debugApi:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'เกิดข้อผิดพลาดในการดึงข้อมูล API',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function debugDatabase(Request $request)
    {
        try {
            // Check if rebate_amount_usd column exists
            $hasRebateColumn = Schema::hasColumn('clients', 'rebate_amount_usd');
            
            // Get current database status
            $totalClients = \App\Models\Client::count();
            $statusCounts = \App\Models\Client::selectRaw('client_status, COUNT(*) as count')
                ->groupBy('client_status')
                ->get()
                ->pluck('count', 'client_status')
                ->toArray();

            // Sample clients
            $sampleClients = \App\Models\Client::select('client_uid', 'client_status', 'rebate_amount_usd', 'last_sync_at')
                ->limit(5)
                ->get();

            // Check table structure
            $columns = Schema::getColumnListing('clients');

            return response()->json([
                'database_info' => [
                    'table_exists' => Schema::hasTable('clients'),
                    'columns' => $columns,
                    'has_rebate_column' => $hasRebateColumn,
                    'total_clients' => $totalClients,
                    'status_distribution' => $statusCounts,
                    'sample_clients' => $sampleClients
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error in debugDatabase:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'เกิดข้อผิดพลาดในการตรวจสอบฐานข้อมูล',
                'message' => $e->getMessage()
            ], 500);
        }
    }
} 