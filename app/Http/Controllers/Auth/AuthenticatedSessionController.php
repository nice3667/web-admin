<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\ExnessClientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Http;

class AuthenticatedSessionController extends Controller
{
    protected $exnessClientService;

    public function __construct(ExnessClientService $exnessClientService)
    {
        $this->exnessClientService = $exnessClientService;
    }
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

        $user = Auth::user();

        // Get Exness token
        try {
            Log::info('Getting Exness token for user', ['user_id' => $user->id]);

            $requestData = [
                'login' => $request->email,
                'password' => $request->password
            ];

            Log::info('Sending request to Exness API', ['request_data' => $requestData]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post('https://my.exnessaffiliates.com/api/v2/auth/', $requestData);

            // Debug response
            $authData = [
                'status' => $response->status(),
                'data' => $response->json(),
                'headers' => $response->headers(),
                'session' => session()->all(),
                'request_data' => $requestData,
                'raw_response' => $response->body()
            ];

            if ($response->successful()) {
                $token = $response->json()['token'];

                // Get client data from both APIs
                $v1Response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json'
                ])->get('https://my.exnessaffiliates.com/api/reports/clients/');

                $v2Response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json'
                ])->get('https://my.exnessaffiliates.com/api/v2/reports/clients/');

                // Add client data to debug output
                $authData['client_data'] = [
                    'v1' => [
                        'status' => $v1Response->status(),
                        'data' => $v1Response->json(),
                        'headers' => $v1Response->headers()
                    ],
                    'v2' => [
                        'status' => $v2Response->status(),
                        'data' => $v2Response->json(),
                        'headers' => $v2Response->headers()
                    ]
                ];
            }

            // dd($authData);

            if ($response->successful()) {
                $tokenData = $response->json();
                $token = $tokenData['token'] ?? null;

                // Log token data
                Log::info('Exness Token Response:', [
                    'user_id' => $user->id,
                    'status' => $response->status(),
                    'token' => $token
                ]);

                if ($token) {
                    // Get clients data using the service
                    $this->exnessClientService->setToken($token);
                    $clientsData = $this->exnessClientService->getAllClients();

                    // Store token and client data in session
                    session([
                        'exness_token' => $token,
                        'exness_token_expires_at' => now()->addHours(6),
                        'exness_sync_status' => 'success',
                        'exness_sync_message' => 'เชื่อมต่อกับ Exness สำเร็จ',
                        'exness_clients_v1' => $clientsData['v1'],
                        'exness_clients_v2' => $clientsData['v2']
                    ]);

                    Log::info('Exness data stored in session', [
                        'user_id' => $user->id,
                        'v1_clients_count' => count($clientsData['v1']['data'] ?? []),
                        'v2_clients_count' => count($clientsData['v2']['data'] ?? [])
                    ]);

                    return redirect('/admin/customers')->with([
                        'api_domain' => request()->getSchemeAndHttpHost(),
                        'message' => 'ล็อกอินสำเร็จ',
                        'clients_data' => $clientsData
                    ]);
                } else {
                    session([
                        'exness_sync_status' => 'failed',
                        'exness_sync_message' => 'ไม่สามารถเชื่อมต่อกับ Exness ได้ กรุณาตรวจสอบบัญชี Exness ของคุณ'
                    ]);
                    Log::warning('Failed to get Exness token - no token in response', ['user_id' => $user->id]);
                }
            } else {
                session([
                    'exness_sync_status' => 'failed',
                    'exness_sync_message' => 'ไม่สามารถเชื่อมต่อกับ Exness ได้ กรุณาตรวจสอบบัญชี Exness ของคุณ'
                ]);
                Log::warning('Failed to get Exness token', [
                    'user_id' => $user->id,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error getting Exness token', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            session([
                'exness_sync_status' => 'error',
                'exness_sync_message' => 'เกิดข้อผิดพลาดในการเชื่อมต่อกับ Exness'
            ]);
        }

        // Force config update for API URL
        Config::set('app.url', request()->getSchemeAndHttpHost());

        Log::info('Login completed, redirecting to dashboard', ['user_id' => $user->id]);

        return redirect('/admin/customers')->with([
            'api_domain' => request()->getSchemeAndHttpHost(),
            'message' => 'ล็อกอินสำเร็จ'
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Log::info('User logging out', ['user_id' => Auth::id()]);

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('Logout completed');

        return redirect('/');
    }
}
