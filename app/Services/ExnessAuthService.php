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
            $token = $this->retrieveToken();
            if (!$token) {
                return ['error' => 'ไม่สามารถรับ token ได้'];
            }

            // Get data from both API versions without limit
            $dataV1 = $this->getClientsFromUrl(
                "https://my.exnessaffiliates.com/api/reports/clients/",
                $token,
                'v1'
            );

            $dataV2 = $this->getClientsFromUrl(
                "https://my.exnessaffiliates.com/api/v2/reports/clients/",
                $token,
                'v2'
            );

            // Create a map of client_uid to v2 data
            $v2Map = [];
            foreach ($dataV2 as $client) {
                if (isset($client['client_uid'])) {
                    $v2Map[$client['client_uid']] = $client;
                }
            }

            // Combine data from both sources
            $combined = [];
            foreach ($dataV1 as $v1Client) {
                $clientUid = $v1Client['client_uid'] ?? null;
                $v2Data = $clientUid ? ($v2Map[$clientUid] ?? []) : [];
                
                $combined[] = array_merge($v1Client, $v2Data);
            }

            // Add any v2 clients that weren't in v1
            foreach ($dataV2 as $v2Client) {
                $clientUid = $v2Client['client_uid'] ?? null;
                if ($clientUid && !isset($v2Map[$clientUid])) {
                    $combined[] = $v2Client;
                }
            }

            // Log the data for debugging
            Log::info('Combined clients data:', [
                'v1_count' => count($dataV1),
                'v2_count' => count($dataV2),
                'total_count' => count($combined),
                'sample_data' => $combined[0] ?? null,
                'v2_map_keys' => array_keys($v2Map)
            ]);

            return ['data' => $combined];

        } catch (\Exception $e) {
            Log::error('Clients data fetch error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['error' => $e->getMessage()];
        }
    }

    private function getClientsFromUrl(string $url, string $token, string $label): array
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'JWT ' . $token
        ])->get($url);

        if (!$response->successful()) {
            Log::error('Failed to fetch clients data:', [
                'url' => $url,
                'status' => $response->status(),
                'response' => $response->json()
            ]);
            return [];
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

        return $data;
    }
} 