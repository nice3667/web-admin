<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ExnessController extends Controller
{
    // ฟังก์ชัน login และดึงข้อมูลลูกค้า
    public function test(Request $request)
    {
        // *** ใส่อีเมลและรหัสผ่านของคุณตรงนี้ หรือรับจาก $request ***
        $loginData = [
            'login' => 'Janischa.trade@gmail.com',   // ต้องใช้ key 'login' ไม่ใช่ 'email'
            'password' => 'Janis@2025',
        ];

        // 1. ขอ JWT Token
        $res = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://my.exnessaffiliates.com/api/v2/auth/", $loginData);

        $token = $res->json()['token'] ?? null;

        if (!$token) {
            return response()->json(['error' => 'ไม่สามารถรับ token ได้'], 500);
        }

        // 2. ดึงข้อมูลลูกค้า
        $res2 = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'JWT ' . $token,
        ])->get("https://my.exnessaffiliates.com/api/v2/reports/clients/");

        if ($res2->failed()) {
            return response()->json(['error' => 'ไม่สามารถดึงข้อมูลลูกค้าได้'], 500);
        }

        return response()->json($res2->json());
    }

    public function getClients(Request $request)
    {
        try {
            // *** ใส่อีเมลและรหัสผ่านของคุณตรงนี้ หรือรับจาก $request ***
            $loginData = [
                'login' => 'Janischa.trade@gmail.com',   // ต้องใช้ key 'login' ไม่ใช่ 'email'
                'password' => 'Janis@2025',
            ];

            // 1. ขอ JWT Token
            $res = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://my.exnessaffiliates.com/api/v2/auth/", $loginData);

            $token = $res->json()['token'] ?? null;

            if (!$token) {
                return response()->json(['error' => 'ไม่สามารถรับ token ได้'], 500);
            }

            // 2. ดึงข้อมูลลูกค้า
            $res2 = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'JWT ' . $token,
            ])->get("https://my.exnessaffiliates.com/api/v2/reports/clients/");

            if ($res2->failed()) {
                return response()->json(['error' => 'ไม่สามารถดึงข้อมูลลูกค้าได้'], 500);
            }

            $data = $res2->json();
            
            // Ensure we're returning an array of clients
            $clients = is_array($data) ? $data : 
                      (isset($data['results']) ? $data['results'] : 
                      (isset($data['data']) ? $data['data'] : []));

            // Ensure all numeric values are properly formatted
            $clients = array_map(function($client) {
                $client['volume_lots'] = floatval($client['volume_lots'] ?? 0);
                $client['volume_mln_usd'] = floatval($client['volume_mln_usd'] ?? 0);
                $client['reward_usd'] = floatval($client['reward_usd'] ?? 0);
                return $client;
            }, $clients);

            return response()->json([
                'data' => $clients,
                'message' => 'Success'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }
}