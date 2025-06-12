<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExnessController extends Controller
{
    public function test(Request $request)
    {
        Log::info($request->all());
    
        // 1. ขอ JWT Token
        $res = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post(
            "https://my.exnessaffiliates.com/api/v2/auth/",
            $request->all()
        );
    
        Log::info('Response:', $res->json());
    
        // Exness API จะคืน token ใน key ชื่อ access_token (ไม่ใช่ token)
        $token = $res->json()['token'] ?? null;
        Log::info('Token: ' . $token);
    
        if (!$token) {
            return response()->json(['error' => 'ไม่สามารถรับ token ได้'], 400);
        }
    
        // 2. ใช้ token ดึงข้อมูล wallet accounts
        $res2 = Http::withHeaders([
            'Content-Type' => 'application/json',
            // Exness ใช้ "JWT " prefix ไม่ใช่ "Bearer "
            'Authorization' => 'JWT ' . $token,
        ])->get("https://my.exnessaffiliates.com/api/wallet/accounts/");
    
        if ($res2->failed()) {
            return response()->json([
                'error' => 'ไม่สามารถดึงข้อมูล wallet accounts ได้',
                'details' => $res2->json(),
            ], 400);
        }
    
        return response()->json($res2->json());
    }
    
    }