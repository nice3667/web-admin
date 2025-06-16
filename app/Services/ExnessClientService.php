<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExnessClientService
{
    protected $baseUrl = 'https://my.exnessaffiliates.com';
    protected $token;

    public function __construct($token = null)
    {
        $this->token = $token;
    }

    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    protected function getHeaders()
    {
        return [
            'Authorization' => 'JWT ' . $this->token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];
    }

    public function getClientsV1()
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->get($this->baseUrl . '/api/reports/clients/');

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Failed to fetch V1 clients', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Error fetching V1 clients', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    public function getClientsV2()
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->get($this->baseUrl . '/api/v2/reports/clients/');

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Failed to fetch V2 clients', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Error fetching V2 clients', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    public function getAllClients()
    {
        $v1Data = $this->getClientsV1();
        $v2Data = $this->getClientsV2();

        return [
            'v1' => $v1Data,
            'v2' => $v2Data
        ];
    }
} 