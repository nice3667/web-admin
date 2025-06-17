<?php

namespace App\Http\Controllers;

use App\Services\ClientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

    public function sync()
    {
        try {
            Log::info('Manual sync requested');
            $success = $this->clientService->syncClients();

            if ($success) {
                Log::info('Manual sync completed successfully');
                return response()->json([
                    'success' => true,
                    'message' => 'อัพเดทข้อมูลลูกค้าสำเร็จ'
                ]);
            }

            Log::error('Manual sync failed');
            return response()->json([
                'success' => false,
                'message' => 'ไม่สามารถอัพเดทข้อมูลลูกค้าได้'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Error in manual sync:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการอัพเดทข้อมูลลูกค้า: ' . $e->getMessage()
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
} 