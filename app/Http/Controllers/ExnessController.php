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
        $user = Auth::user();
        return Inertia::render('Admin/Exness/Credentials', [
            'user' => $user
        ]);
    }

    public function updateCredentials(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exness_email' => 'required|email',
            'exness_password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();
        $user->exness_email = $request->exness_email;
        $user->exness_password_encrypted = Crypt::encryptString($request->exness_password);
        $user->save();

        return back()->with('success', 'อัปเดตข้อมูล Exness สำเร็จ');
    }

    public function getToken()
    {
        try {
            $user = Auth::user();
            
            if (!$user->exness_email || !$user->exness_password_encrypted) {
                return response()->json([
                    'error' => 'กรุณาตั้งค่า Exness credentials ก่อน'
                ], 400);
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post('https://api.exness.com/v1/auth/token', [
                'email' => $user->exness_email,
                'password' => Crypt::decryptString($user->exness_password_encrypted)
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'token' => $data['token'],
                    'user' => $data['user']
                ]);
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

    public function clients()
    {
        try {
            $user = Auth::user();
            
            if (!$user->exness_email || !$user->exness_password_encrypted) {
                return response()->json([
                    'error' => 'กรุณาตั้งค่า Exness credentials ก่อน'
                ], 400);
            }

            $tokenResponse = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post('https://api.exness.com/v1/auth/token', [
                'email' => $user->exness_email,
                'password' => Crypt::decryptString($user->exness_password_encrypted)
            ]);

            if (!$tokenResponse->successful()) {
                return response()->json([
                    'error' => 'ไม่สามารถดึง Token ได้',
                    'message' => $tokenResponse->json()['message'] ?? 'เกิดข้อผิดพลาดที่ไม่ทราบสาเหตุ'
                ], $tokenResponse->status());
            }

            $token = $tokenResponse->json()['token'];

            $clientsResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json'
            ])->get('https://api.exness.com/v1/clients');

            if ($clientsResponse->successful()) {
                return response()->json([
                    'clients' => $clientsResponse->json()
                ]);
            }

            return response()->json([
                'error' => 'ไม่สามารถดึงข้อมูลลูกค้าได้',
                'message' => $clientsResponse->json()['message'] ?? 'เกิดข้อผิดพลาดที่ไม่ทราบสาเหตุ'
            ], $clientsResponse->status());

        } catch (\Exception $e) {
            Log::error('Exness Clients Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'เกิดข้อผิดพลาดในการดึงข้อมูลลูกค้า',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function clientsV2()
    {
        try {
            $user = Auth::user();
            
            if (!$user->exness_email || !$user->exness_password_encrypted) {
                return response()->json([
                    'error' => 'กรุณาตั้งค่า Exness credentials ก่อน'
                ], 400);
            }

            $tokenResponse = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post('https://api.exness.com/v1/auth/token', [
                'email' => $user->exness_email,
                'password' => Crypt::decryptString($user->exness_password_encrypted)
            ]);

            if (!$tokenResponse->successful()) {
                return response()->json([
                    'error' => 'ไม่สามารถดึง Token ได้',
                    'message' => $tokenResponse->json()['message'] ?? 'เกิดข้อผิดพลาดที่ไม่ทราบสาเหตุ'
                ], $tokenResponse->status());
            }

            $token = $tokenResponse->json()['token'];

            $clientsResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json'
            ])->get('https://api.exness.com/v1/clients');

            if ($clientsResponse->successful()) {
                $clients = $clientsResponse->json();
                
                // ดึงข้อมูล wallet accounts สำหรับแต่ละ client
                $clientsWithWallets = [];
                foreach ($clients as $client) {
                    $walletResponse = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $token,
                        'Accept' => 'application/json'
                    ])->get("https://api.exness.com/v1/clients/{$client['id']}/wallets");

                    if ($walletResponse->successful()) {
                        $client['wallets'] = $walletResponse->json();
                    } else {
                        $client['wallets'] = [];
                        Log::error("Failed to fetch wallets for client {$client['id']}: " . $walletResponse->body());
                    }
                    
                    $clientsWithWallets[] = $client;
                }

                return response()->json([
                    'clients' => $clientsWithWallets
                ]);
            }

            return response()->json([
                'error' => 'ไม่สามารถดึงข้อมูลลูกค้าได้',
                'message' => $clientsResponse->json()['message'] ?? 'เกิดข้อผิดพลาดที่ไม่ทราบสาเหตุ'
            ], $clientsResponse->status());

        } catch (\Exception $e) {
            Log::error('Exness Clients V2 Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'เกิดข้อผิดพลาดในการดึงข้อมูลลูกค้า',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getWalletAccounts()
    {
        try {
            $user = Auth::user();
            
            if (!$user->exness_email || !$user->exness_password_encrypted) {
                return response()->json([
                    'error' => 'กรุณาตั้งค่า Exness credentials ก่อน'
                ], 400);
            }

            $tokenResponse = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post('https://api.exness.com/v1/auth/token', [
                'email' => $user->exness_email,
                'password' => Crypt::decryptString($user->exness_password_encrypted)
            ]);

            if (!$tokenResponse->successful()) {
                return response()->json([
                    'error' => 'ไม่สามารถดึง Token ได้',
                    'message' => $tokenResponse->json()['message'] ?? 'เกิดข้อผิดพลาดที่ไม่ทราบสาเหตุ'
                ], $tokenResponse->status());
            }

            $token = $tokenResponse->json()['token'];

            $clientsResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json'
            ])->get('https://api.exness.com/v1/clients');

            if (!$clientsResponse->successful()) {
                return response()->json([
                    'error' => 'ไม่สามารถดึงข้อมูลลูกค้าได้',
                    'message' => $clientsResponse->json()['message'] ?? 'เกิดข้อผิดพลาดที่ไม่ทราบสาเหตุ'
                ], $clientsResponse->status());
            }

            $clients = $clientsResponse->json();
            $allWallets = [];

            foreach ($clients as $client) {
                $walletResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json'
                ])->get("https://api.exness.com/v1/clients/{$client['id']}/wallets");

                if ($walletResponse->successful()) {
                    $wallets = $walletResponse->json();
                    foreach ($wallets as $wallet) {
                        $allWallets[] = [
                            'client_id' => $client['id'],
                            'client_name' => $client['name'],
                            'wallet_id' => $wallet['id'],
                            'wallet_name' => $wallet['name'],
                            'balance' => $wallet['balance'] ?? 0,
                            'currency' => $wallet['currency'] ?? 'USD'
                        ];
                    }
                } else {
                    Log::error("Failed to fetch wallets for client {$client['id']}: " . $walletResponse->body());
                }
            }

            return response()->json([
                'wallets' => $allWallets
            ]);

        } catch (\Exception $e) {
            Log::error('Exness Wallet Accounts Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'เกิดข้อผิดพลาดในการดึงข้อมูลบัญชี',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
