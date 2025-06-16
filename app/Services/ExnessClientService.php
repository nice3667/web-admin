<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExnessClientService
{
    public function fetchClients(string $token)
    {
        try {
            $client = new \GuzzleHttp\Client([
                'headers' => [
                    'Authorization' => 'JWT ' . $token,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'verify' => false,
                'timeout' => 30,
                'connect_timeout' => 10
            ]);

            // ดึงข้อมูลจากทั้งสอง API พร้อมกัน
            $promises = [
                'v1' => $client->getAsync(config('exness.api_base') . '/api/reports/clients/?limit=100'),
                'v2' => $client->getAsync(config('exness.api_base') . '/api/v2/reports/clients/?limit=100')
            ];

            // รอให้ทั้งสอง request เสร็จสิ้น
            $responses = \GuzzleHttp\Promise\Utils::unwrap($promises);

            // แปลงข้อมูลจาก V1 API
            $v1Data = json_decode($responses['v1']->getBody(), true);
            Log::info('V1 API Response:', ['data' => $v1Data]);

            // แปลงข้อมูลจาก V2 API
            $v2Data = json_decode($responses['v2']->getBody(), true);
            Log::info('V2 API Response:', ['data' => $v2Data]);

            // เพิ่ม source label ให้กับข้อมูลแต่ละเส้น
            $v1Clients = [];
            if (isset($v1Data['data']) && is_array($v1Data['data'])) {
                foreach ($v1Data['data'] as $client) {
                    $client['source'] = 'v1';
                    $v1Clients[] = $client;
                }
            }

            $v2Clients = [];
            if (isset($v2Data['data']) && is_array($v2Data['data'])) {
                foreach ($v2Data['data'] as $client) {
                    $client['source'] = 'v2';
                    $v2Clients[] = $client;
                }
            }

            return [
                'data_v1' => $v1Clients,
                'data_v2' => $v2Clients
            ];

        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            Log::error('Connection Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Connection error: ' . $e->getMessage());
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error('Request Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Request error: ' . $e->getMessage());
        }
    }
} 