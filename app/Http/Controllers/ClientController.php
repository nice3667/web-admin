<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ExnessClientService;

class ClientController extends Controller
{
    protected $exnessClientService;

    public function __construct(ExnessClientService $exnessClientService)
    {
        $this->exnessClientService = $exnessClientService;
    }

    public function index(Request $request)
    {
        $token = session('exness_token');
        
        if (!$token) {
            return response()->json([
                'error' => 'No Exness token found'
            ], 401);
        }

        $this->exnessClientService->setToken($token);
        $clientsData = $this->exnessClientService->getAllClients();

        // Debug raw API response
        dd([
            'session_token' => $token,
            'raw_v1_data' => $clientsData['v1'] ?? [],
            'raw_v2_data' => $clientsData['v2'] ?? [],
            'session_data' => session()->all()
        ]);

        // Combine and format client data
        $formattedClients = [];
        
        // Process V1 clients
        if (isset($clientsData['v1']['data'])) {
            foreach ($clientsData['v1']['data'] as $client) {
                $formattedClients[] = [
                    'id' => $client['id'] ?? null,
                    'client_id' => $client['client_id'] ?? null,
                    'name' => $client['name'] ?? null,
                    'email' => $client['email'] ?? null,
                    'status' => $client['status'] ?? null,
                    'balance' => $client['balance'] ?? 0,
                    'source' => 'V1'
                ];
            }
        }

        // Process V2 clients
        if (isset($clientsData['v2']['data'])) {
            foreach ($clientsData['v2']['data'] as $client) {
                $formattedClients[] = [
                    'id' => $client['id'] ?? null,
                    'client_id' => $client['client_id'] ?? null,
                    'name' => $client['name'] ?? null,
                    'email' => $client['email'] ?? null,
                    'status' => $client['status'] ?? null,
                    'balance' => $client['balance'] ?? 0,
                    'source' => 'V2'
                ];
            }
        }

        return response()->json($formattedClients);
    }
} 