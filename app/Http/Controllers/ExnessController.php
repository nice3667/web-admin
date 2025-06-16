<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;

class ExnessController extends Controller
{
    public function credentials()
    {
        return Inertia::render('Admin/Exness/Credentials');
    }

    public function updateCredentials(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);

        $user = Auth::user();
        $user->exness_email = $request->email;
        $user->exness_password_encrypted = Crypt::encryptString($request->password);
        $user->save();

        return back()->with('success', 'อัปเดตข้อมูล Exness สำเร็จ');
    }

    public function getToken()
    {
        try {
            // Try to get token from session first
            if (session()->has('exness_token')) {
                return response()->json([
                    'token' => session('exness_token'),
                    'source' => 'session'
                ]);
            }

            // If no session token, try with stored credentials
            $user = Auth::user();
            
            if (!$user->exness_email || !$user->exness_password_encrypted) {
                return response()->json([
                    'error' => 'กรุณา login ด้วย Exness credentials หรือตั้งค่า credentials ก่อน'
                ], 400);
            }

            $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            ])->post('https://my.exnessaffiliates.com/api/v2/auth/', [
                'login' => $user->exness_email,
                'password' => Crypt::decryptString($user->exness_password_encrypted)
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $token = $data['token'] ?? null;
                
                if ($token) {
                    // Store in session
                    session(['exness_token' => $token]);
                    
                    return response()->json([
                        'token' => $token,
                        'source' => 'api'
                    ]);
                }
            }

            return response()->json([
                'error' => 'ไม่สามารถดึง Token ได้',
                'message' => $response->json()['message'] ?? 'เกิดข้อผิดพลาดที่ไม่ทราบสาเหตุ'
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('Exness Token Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'เกิดข้อผิดพลาดในการดึง Token',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getClients(Request $request)
    {
        try {
            // Get token from session or credentials
            $token = $this->getValidToken();

            if (!$token) {
                return response()->json([
                    'error' => 'ไม่สามารถดึง Token ได้ กรุณา login ใหม่'
                ], 401);
            }

            Log::info('Getting clients from both V1 and V2 APIs');

            // Get data from both V1 and V2 APIs with retry logic
            $v1Response = $this->callExnessApiWithRetry('https://my.exnessaffiliates.com/api/reports/clients/?limit=100', $token);
            $v2Response = $this->callExnessApiWithRetry('https://my.exnessaffiliates.com/api/v2/reports/clients/?limit=100', $token);

            $result = [
                'data_v1' => null,
                'data_v2' => null,
                'debug' => [
                    'v1_status' => $v1Response ? $v1Response->status() : 'failed',
                    'v2_status' => $v2Response ? $v2Response->status() : 'failed',
                    'timestamp' => now()->toISOString(),
                    'user_id' => Auth::id(),
                    'token_used' => substr($token, 0, 20) . '...'
                ]
            ];

            if ($v1Response && $v1Response->successful()) {
                $v1Data = $v1Response->json();
                // Extract the data array from V1 response
                $result['data_v1'] = $v1Data['data'] ?? [];
                Log::info('V1 API successful', ['data_count' => count($result['data_v1'])]);
            } else {
                Log::error('V1 API failed', [
                    'status' => $v1Response ? $v1Response->status() : 'no response',
                    'response' => $v1Response ? $v1Response->body() : 'no response'
                ]);
            }

            if ($v2Response && $v2Response->successful()) {
                $v2Data = $v2Response->json();
                // Extract the data array from V2 response
                $result['data_v2'] = $v2Data['data'] ?? [];
                Log::info('V2 API successful', ['data_count' => count($result['data_v2'])]);
            } else {
                Log::error('V2 API failed', [
                    'status' => $v2Response ? $v2Response->status() : 'no response',
                    'response' => $v2Response ? $v2Response->body() : 'no response'
                ]);
            }

            // If both APIs failed
            if ((!$v1Response || !$v1Response->successful()) && (!$v2Response || !$v2Response->successful())) {
                return response()->json([
                    'error' => 'ไม่สามารถดึงข้อมูลลูกค้าได้',
                    'v1_error' => $v1Response ? $v1Response->json()['message'] ?? 'V1 API failed' : 'No response',
                    'v2_error' => $v2Response ? $v2Response->json()['message'] ?? 'V2 API failed' : 'No response',
                    'debug' => $result['debug']
                ], 500);
            }

            Log::info('Clients data retrieved successfully', [
                'v1_success' => $v1Response && $v1Response->successful(),
                'v2_success' => $v2Response && $v2Response->successful(),
                'v1_count' => count($result['data_v1'] ?? []),
                'v2_count' => count($result['data_v2'] ?? []),
                'user_id' => Auth::id()
            ]);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Exness Get Clients Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id() ?? 'not_authenticated'
            ]);
            return response()->json([
                'error' => 'เกิดข้อผิดพลาดในการดึงข้อมูลลูกค้า',
                'message' => $e->getMessage(),
                'debug' => [
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine(),
                    'authenticated' => Auth::check(),
                    'user_id' => Auth::id()
                ]
            ], 500);
        }
    }

    public function getWallets()
    {
        try {
            $token = $this->getValidToken();
            
            if (!$token) {
                return response()->json([
                    'error' => 'ไม่สามารถดึง Token ได้ กรุณา login ใหม่'
                ], 401);
            }

            // Get clients data from V2 API
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'JWT ' . $token,
            ])->get('https://my.exnessaffiliates.com/api/v2/reports/clients/?limit=100');

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'error' => 'ไม่สามารถดึงข้อมูล wallet ได้',
                'message' => $response->json()['message'] ?? 'เกิดข้อผิดพลาดที่ไม่ทราบสาเหตุ'
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('Exness Get Wallets Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'เกิดข้อผิดพลาดในการดึงข้อมูล wallet',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getWalletAccounts(Request $request)
    {
        try {
            Log::info('Starting getWalletAccounts request', [
                'user_id' => Auth::id(),
                'user_email' => Auth::user()?->email,
                'has_session_token' => session()->has('exness_token'),
                'has_credentials' => session()->has('exness_credentials'),
                'session_id' => session()->getId()
            ]);
            
            // Check if user is authenticated
            if (!Auth::check()) {
                Log::error('User not authenticated');
                return response()->json([
                    'error' => 'กรุณา login ก่อนใช้งาน',
                    'debug' => [
                        'authenticated' => false,
                        'has_session_token' => session()->has('exness_token'),
                        'has_credentials' => session()->has('exness_credentials')
                    ]
                ], 401);
            }
            
            $token = $this->getValidToken();
            
            if (!$token) {
                Log::error('No valid token available', [
                    'user_id' => Auth::id(),
                    'has_session_token' => session()->has('exness_token'),
                    'has_credentials' => session()->has('exness_credentials')
                ]);
                
                return response()->json([
                    'error' => 'ไม่สามารถดึง Token ได้ กรุณา login ใหม่',
                    'debug' => [
                        'authenticated' => Auth::check(),
                        'user_id' => Auth::id(),
                        'has_session_token' => session()->has('exness_token'),
                        'has_credentials' => session()->has('exness_credentials'),
                        'session_id' => session()->getId()
                    ]
                ], 401);
            }

            Log::info('Using token for API calls', [
                'token_length' => strlen($token),
                'user_id' => Auth::id()
            ]);

            // Get data from both V1 and V2 APIs with retry logic
            $v1Response = $this->callExnessApiWithRetry('https://my.exnessaffiliates.com/api/reports/clients/?limit=100', $token);
            $v2Response = $this->callExnessApiWithRetry('https://my.exnessaffiliates.com/api/v2/reports/clients/?limit=100', $token);

            $result = [
                'v1_data' => null,
                'v2_data' => null,
                'combined_wallets' => [],
                'debug' => [
                    'v1_status' => $v1Response ? $v1Response->status() : 'failed',
                    'v2_status' => $v2Response ? $v2Response->status() : 'failed',
                    'timestamp' => now()->toISOString(),
                    'user_id' => Auth::id(),
                    'token_used' => substr($token, 0, 20) . '...'
                ]
            ];

            if ($v1Response && $v1Response->successful()) {
                $v1Data = $v1Response->json();
                $result['v1_data'] = $v1Data;
                
                if (isset($v1Data['data']) && is_array($v1Data['data'])) {
                    foreach ($v1Data['data'] as $client) {
                        $result['combined_wallets'][] = [
                            'source' => 'v1',
                            'client_id' => $client['id'] ?? null,
                            'client_name' => $client['name'] ?? 'Unknown',
                            'email' => $client['email'] ?? '',
                            'balance' => $client['balance'] ?? 0,
                            'currency' => $client['currency'] ?? 'USD',
                            'registration_date' => $client['registration_date'] ?? null,
                            'last_activity' => $client['last_activity'] ?? null
                        ];
                    }
                }
            } else {
                Log::error('V1 API failed', [
                    'status' => $v1Response ? $v1Response->status() : 'no response',
                    'response' => $v1Response ? $v1Response->body() : 'no response'
                ]);
            }

            if ($v2Response && $v2Response->successful()) {
                $v2Data = $v2Response->json();
                $result['v2_data'] = $v2Data;
                
                if (isset($v2Data['data']) && is_array($v2Data['data'])) {
                    foreach ($v2Data['data'] as $client) {
                        $result['combined_wallets'][] = [
                            'source' => 'v2',
                            'client_id' => $client['id'] ?? null,
                            'client_name' => $client['name'] ?? 'Unknown',
                            'email' => $client['email'] ?? '',
                            'balance' => $client['balance'] ?? 0,
                            'currency' => $client['currency'] ?? 'USD',
                            'registration_date' => $client['registration_date'] ?? null,
                            'last_activity' => $client['last_activity'] ?? null
                        ];
                    }
                }
            } else {
                Log::error('V2 API failed', [
                    'status' => $v2Response ? $v2Response->status() : 'no response',
                    'response' => $v2Response ? $v2Response->body() : 'no response'
                ]);
            }

            // If both APIs failed
            if ((!$v1Response || !$v1Response->successful()) && (!$v2Response || !$v2Response->successful())) {
                return response()->json([
                    'error' => 'ไม่สามารถดึงข้อมูลบัญชีได้',
                    'v1_error' => $v1Response ? $v1Response->json()['message'] ?? 'V1 API failed' : 'No response',
                    'v2_error' => $v2Response ? $v2Response->json()['message'] ?? 'V2 API failed' : 'No response',
                    'debug' => $result['debug']
                ], 500);
            }

            Log::info('Wallet accounts retrieved successfully', [
                'v1_success' => $v1Response && $v1Response->successful(),
                'v2_success' => $v2Response && $v2Response->successful(),
                'total_wallets' => count($result['combined_wallets']),
                'user_id' => Auth::id()
            ]);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Exness Wallet Accounts Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id() ?? 'not_authenticated'
            ]);
            return response()->json([
                'error' => 'เกิดข้อผิดพลาดในการดึงข้อมูลบัญชี',
                'message' => $e->getMessage(),
                'debug' => [
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine(),
                    'authenticated' => Auth::check(),
                    'user_id' => Auth::id()
                ]
            ], 500);
        }
    }

    private function callExnessApiWithRetry($url, $token, $maxRetries = 2)
    {
        for ($i = 0; $i < $maxRetries; $i++) {
            try {
                $response = Http::timeout(30)->withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'JWT ' . $token,
                ])->get($url);

                // If token expired, try to refresh and retry
                if ($response->status() === 401 && $i < $maxRetries - 1) {
                    Log::info('Token expired, attempting to refresh', ['attempt' => $i + 1]);
                    $newToken = $this->refreshToken();
                    if ($newToken) {
                        $token = $newToken;
                        continue;
                    }
                }

                return $response;
            } catch (\Exception $e) {
                Log::error('API call failed', [
                    'url' => $url,
                    'attempt' => $i + 1,
                    'error' => $e->getMessage()
                ]);
                
                if ($i === $maxRetries - 1) {
                    throw $e;
                }
            }
        }

        return null;
    }

    private function refreshToken()
    {
        if (session()->has('exness_credentials')) {
            $credentials = session('exness_credentials');
            
            try {
                Log::info('Attempting to refresh token');
                
                $response = Http::timeout(30)->withHeaders([
                    'Content-Type' => 'application/json',
                ])->post('https://my.exnessaffiliates.com/api/v2/auth/', [
                    'login' => $credentials['email'],
                    'password' => $credentials['password']
                ]);

                if ($response->successful()) {
                    $token = $response->json()['token'] ?? null;
                    if ($token) {
                        session(['exness_token' => $token]);
                        Log::info('Token refreshed successfully');
                        return $token;
                    }
                }
                
                Log::error('Token refresh failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
        } catch (\Exception $e) {
                Log::error('Token refresh exception: ' . $e->getMessage());
            }
        }

        return null;
    }

    private function getValidToken()
    {
        // First try session token
        if (session()->has('exness_token')) {
            return session('exness_token');
        }

        // Try to refresh token
        return $this->refreshToken();
    }

    public function saveCredentials(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        try {
            // Test credentials
            $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
            ])->post('https://my.exnessaffiliates.com/api/v2/auth/', [
                'login' => $request->email,
                'password' => $request->password
            ]);

            if ($response->successful()) {
                $token = $response->json()['token'] ?? null;
                
                if ($token) {
                    // Store in session
                    session([
                        'exness_token' => $token,
                        'exness_credentials' => [
                            'email' => $request->email,
                            'password' => $request->password
                        ]
                    ]);

                    // Also store in user record
                    $user = Auth::user();
                    $user->exness_email = $request->email;
                    $user->exness_password_encrypted = Crypt::encryptString($request->password);
                    $user->save();

                return response()->json([
                    'success' => true,
                        'message' => 'บันทึก credentials สำเร็จ',
                        'token' => $token
                    ]);
                }
            }

            return response()->json([
                'error' => 'Credentials ไม่ถูกต้อง',
                'message' => $response->json()['message'] ?? 'ไม่สามารถ login ได้'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Save credentials error: ' . $e->getMessage());
            return response()->json([
                'error' => 'เกิดข้อผิดพลาด',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
