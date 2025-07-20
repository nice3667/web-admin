<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\KantapongExnessAuthService;
use Illuminate\Support\Facades\Log;

class TestKantapongConnection extends Command
{
    protected $signature = 'test:kantapong-connection';
    protected $description = 'Test Kantapong Exness API connection and authentication';

    public function handle()
    {
        $this->info('🔍 Testing Kantapong Exness Connection');
        $this->info('=====================================');

        $service = new KantapongExnessAuthService();
        
        // Test authentication
        $this->info("\nTesting authentication...");
        $token = $service->authenticate();

        if ($token) {
            $this->info("✅ Authentication successful!");
            $this->info("Token: " . substr($token, 0, 20) . "...");

            // Test clients API
            $this->info("\nTesting clients API...");
            $clientsData = $service->getClientsData();

            if (!isset($clientsData['error'])) {
                $count = isset($clientsData['data']) ? count($clientsData['data']) : 0;
                $this->info("✅ Found {$count} clients");
                
                if ($count > 0) {
                    $this->info("\nSample client data:");
                    $sample = array_slice($clientsData['data'], 0, 1)[0];
                    $this->table(
                        ['Field', 'Value'],
                        collect($sample)
                            ->map(fn($v, $k) => [$k, is_array($v) ? json_encode($v) : $v])
                            ->take(5)
                            ->toArray()
                    );
                }
            } else {
                $this->error("❌ Failed to get clients data: " . $clientsData['error']);
            }
        } else {
            $this->error("❌ Authentication failed!");
            $this->info("\nPlease verify:");
            $this->info("1. Email is correct (current: {$service->getEmail()})");
            $this->info("2. Password is correct");
            $this->info("3. Account is active");
            $this->info("4. No IP restrictions");
            
            // Check Laravel logs
            $this->info("\nChecking Laravel logs for errors...");
            $logPath = storage_path('logs/laravel.log');
            if (file_exists($logPath)) {
                $logs = array_slice(array_filter(
                    explode("\n", file_get_contents($logPath)),
                    fn($line) => str_contains($line, 'Kantapong')
                ), -5);
                
                foreach ($logs as $log) {
                    $this->line($log);
                }
            } else {
                $this->warn("No log file found at: " . $logPath);
            }
        }
    }
} 