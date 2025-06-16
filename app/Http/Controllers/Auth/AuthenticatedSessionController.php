<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        Log::info('Login attempt started', ['email' => $request->email]);
        
        $request->authenticate();
        $request->session()->regenerate();

        Log::info('Laravel authentication successful');

        // Always try to get Exness token
        try {
            Log::info('Attempting to get Exness token');
            
            // Primary attempt with user's credentials
            $response = Http::timeout(30)->withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://my.exnessaffiliates.com/api/v2/auth/', [
                'login' => $request->email,
                'password' => $request->password
            ]);

            Log::info('Exness API response for user credentials', [
                'email' => $request->email,
                'status' => $response->status(),
                'successful' => $response->successful()
            ]);

            $token = null;
            $usedCredentials = [
                'email' => $request->email,
                'password' => $request->password
            ];

            if ($response->successful()) {
                $tokenData = $response->json();
                $token = $tokenData['token'] ?? null;
                
                if ($token) {
                    Log::info('Exness token obtained with user credentials');
                }
            }

            // Fallback: If user's credentials don't work, try default working credentials
            if (!$token) {
                Log::warning('User Exness credentials failed, trying fallback credentials');
                
                $fallbackResponse = Http::timeout(30)->withHeaders([
                    'Content-Type' => 'application/json',
                ])->post('https://my.exnessaffiliates.com/api/v2/auth/', [
                    'login' => 'kantapong0592@gmail.com',
                    'password' => 'Kantapong.0592z'
                ]);

                Log::info('Fallback Exness API response', [
                    'status' => $fallbackResponse->status(),
                    'successful' => $fallbackResponse->successful()
                ]);

                if ($fallbackResponse->successful()) {
                    $fallbackTokenData = $fallbackResponse->json();
                    $token = $fallbackTokenData['token'] ?? null;
                    
                    if ($token) {
                        // Use fallback credentials for API calls
                        $usedCredentials = [
                            'email' => 'kantapong0592@gmail.com',
                            'password' => 'Kantapong.0592z'
                        ];
                        Log::info('Exness token obtained with fallback credentials', [
                            'user_email' => $request->email,
                            'fallback_used' => true
                        ]);
                    }
                }
            }
            
            if ($token) {
                // Store token and credentials in session
                session([
                    'exness_token' => $token,
                    'exness_credentials' => $usedCredentials,
                    'api_domain' => request()->getSchemeAndHttpHost(),
                    'token_created_at' => now()->toISOString(),
                    'fallback_used' => $usedCredentials['email'] !== $request->email
                ]);

                Log::info('Exness token stored successfully', [
                    'user_email' => $request->email,
                    'exness_email' => $usedCredentials['email'],
                    'token_length' => strlen($token),
                    'fallback_used' => $usedCredentials['email'] !== $request->email,
                    'api_domain' => request()->getSchemeAndHttpHost()
                ]);
            } else {
                Log::error('Failed to get Exness token with both user and fallback credentials', [
                    'user_email' => $request->email,
                    'user_response_status' => $response->status(),
                    'fallback_response_status' => isset($fallbackResponse) ? $fallbackResponse->status() : 'not_attempted'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error getting Exness token during login', [
                'error' => $e->getMessage(),
                'email' => $request->email,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }

        // Force config update for API URL
        Config::set('app.url', request()->getSchemeAndHttpHost());

        Log::info('Login completed, redirecting to dashboard');
        
        return redirect()->route('admin.dashboard')->with([
            'api_domain' => request()->getSchemeAndHttpHost(),
            'message' => 'Successfully logged in',
            'exness_status' => session()->has('exness_token') ? 'connected' : 'not_connected'
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Log::info('User logging out');
        
        // Clear all Exness related session data
        session()->forget([
            'exness_token', 
            'exness_credentials', 
            'api_domain',
            'token_created_at'
        ]);
        
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('Logout completed');

        return redirect('/');
    }
}
