<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExnessAuthService
{
    public function retrieveToken(): ?string
    {
        try {
            $user = Auth::user();
            if (!$user || !$user->exness_email || !$user->exness_password_encrypted) {
                return null;
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Origin' => 'https://my.exnessaffiliates.com',
                'Referer' => 'https://my.exnessaffiliates.com/'
            ])->asForm()
              ->post('https://my.exnessaffiliates.com/api/v2/auth/', [
                'login' => $user->exness_email,
                'password' => decrypt($user->exness_password_encrypted)
              ]);

            Log::info('Token request:', [
                'url' => 'https://my.exnessaffiliates.com/api/v2/auth/',
                'login' => $user->exness_email,
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
} 