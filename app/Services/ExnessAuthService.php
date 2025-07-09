<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExnessAuthService
{
    private string $email = 'Janischa.trade@gmail.com';
    private string $password = 'Janis@2025';

    public function retrieveToken(): ?string
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Origin' => 'https://my.exnessaffiliates.com',
                'Referer' => 'https://my.exnessaffiliates.com/'
            ])->post('https://my.exnessaffiliates.com/api/v2/auth/', [
                'login' => $this->email,
                'password' => $this->password
            ]);

            Log::info('Token request:', [
                'url' => 'https://my.exnessaffiliates.com/api/v2/auth/',
                'login' => $this->email,
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            if ($response->successful()) {
                return $response->json()['token'] ?? null;
            }

            Log::error('Token fetch failed:', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Token fetch error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    public function getClientsData(): array
    {
        try {
            // Check cache first
            $cacheKey = 'exness_clients_data';
            $cachedData = \Cache::get($cacheKey);

            if ($cachedData) {
                Log::info('Exness Using cached clients data');
                return $cachedData;
            }

            $token = $this->retrieveToken();
            if (!$token) {
                return ['error' => 'ไม่สามารถรับ token ได้'];
            }

            // Get data from both API versions without limit with reduced timeout
            $dataV1 = $this->getClientsFromUrl(
                "https://my.exnessaffiliates.com/api/reports/clients/",
                'v1'
            );

            $dataV2 = $this->getClientsFromUrl(
                "https://my.exnessaffiliates.com/api/v2/reports/clients/",
                'v2'
            );

            // Create a map of client_uid to v2 data
            $v2Map = [];
            foreach ($dataV2['data'] as $client) {
                if (isset($client['client_uid'])) {
                    $v2Uid = $client['client_uid'];

                    // Extract short UID from V2 UUID format
                    // V2 format: "3bf4e479-a9df-422a-a0a9-2c1376488f15"
                    // V1 format: "3bf4e479"
                    $shortUid = explode('-', $v2Uid)[0] ?? $v2Uid;

                    // Store with short UID as key for matching with V1
                    $v2Map[$shortUid] = $client;
                }
            }

            // Combine data from both sources
            $combined = [];
            foreach ($dataV1['data'] as $v1Client) {
                $clientUid = $v1Client['client_uid'] ?? null;
                $v2Data = $clientUid ? ($v2Map[$clientUid] ?? []) : [];

                $combined[] = array_merge($v1Client, $v2Data);
            }

            // Log the combined data
            Log::info('Combined V1+V2 clients data:', [
                'v1_count' => count($dataV1['data']),
                'v2_count' => count($dataV2['data']),
                'combined_count' => count($combined),
                'unique_client_uids' => count(array_unique(array_column($combined, 'client_uid'))),
                'sample_data' => $combined[0] ?? null
            ]);

            $result = ['data' => $combined];

            // Cache the result for 5 minutes
            \Cache::put($cacheKey, $result, 300);

            return $result;

        } catch (\Exception $e) {
            Log::error('Error getting clients data:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['error' => $e->getMessage()];
        }
    }

    public function getClientsFromUrl(string $url, string $label = 'api'): array
    {
        try {
            $token = $this->retrieveToken();
            if (!$token) {
                return ['error' => 'ไม่สามารถรับ token ได้'];
            }

            Log::info("Fetching clients from URL ($label)", ['url' => $url]);

            $response = Http::timeout(30) // Reduced from 60 to 30 seconds
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])
                ->get($url);

            if (!$response->successful()) {
                Log::error('Failed to fetch clients data:', [
                    'url' => $url,
                    'label' => $label,
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                return ['error' => 'API call failed: ' . $response->status()];
            }

            $data = $response->json()['data'] ?? [];

            // Log the raw data for debugging
            Log::info("Raw {$label} data:", [
                'url' => $url,
                'count' => count($data),
                'sample' => $data[0] ?? null,
                'fields' => $data[0] ? array_keys($data[0]) : []
            ]);

            // Ensure country field is present
            foreach ($data as &$item) {
                if (!isset($item['client_country']) && isset($item['country'])) {
                    $item['client_country'] = $item['country'];
                }
            }

            return ['data' => $data];

        } catch (\Exception $e) {
            Log::error("Error fetching {$label} clients data:", [
                'url' => $url,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['error' => $e->getMessage()];
        }
    }
}
