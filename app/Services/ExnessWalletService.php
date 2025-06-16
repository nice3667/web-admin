<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExnessWalletService
{
    public function fetchWallets(string $token)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'JWT ' . $token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->get(config('exness.api_base') . '/api/v2/wallets/');

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Wallet fetch failed:', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            throw new \Exception('Failed to fetch wallets: ' . ($response->json()['message'] ?? 'Unknown error'));

        } catch (\Exception $e) {
            Log::error('Wallet fetch error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
} 