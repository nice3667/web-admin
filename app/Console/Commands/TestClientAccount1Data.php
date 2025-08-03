<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Admin\Report1Controller;
use App\Services\HamExnessAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestClientAccount1Data extends Command
{
    protected $signature = 'test:client-account1-data';
    protected $description = 'Test ClientAccount1 data and stats';

    public function handle()
    {
        $this->info('🧪 Testing ClientAccount1 Data and Stats');
        $this->info('=====================================');

        try {
            $controller = new Report1Controller(new HamExnessAuthService());
            $request = new Request();
            
            $this->info('1️⃣ Testing Controller method...');
            $response = $controller->clientAccount1($request);
            
            if ($response instanceof \Inertia\Response) {
                $this->info('✅ Controller returned Inertia Response');
                
                // Get the props from the response
                $props = $response->toResponse($request)->getContent();
                $props = json_decode($props, true);
                
                // Debug: Show the structure
                $this->info('Debug: Response structure:');
                $this->info(json_encode($props, JSON_PRETTY_PRINT));
                
                if (isset($props['props']['stats'])) {
                    $stats = $props['props']['stats'];
                    $this->info('2️⃣ Stats data:');
                    $this->info("   Total accounts: {$stats['total_accounts']}");
                    $this->info("   Total volume lots: {$stats['total_volume_lots']}");
                    $this->info("   Total volume USD: {$stats['total_volume_usd']}");
                    $this->info("   Total profit: {$stats['total_profit']}");
                    $this->info("   Total client UIDs: {$stats['total_client_uids']}");
                } else {
                    $this->error('❌ No stats data found in response');
                }
                
                if (isset($props['props']['clients']['data'])) {
                    $clients = $props['props']['clients']['data'];
                    $this->info("3️⃣ Clients data count: " . count($clients));
                    
                    if (!empty($clients)) {
                        $sampleClient = $clients[0];
                        $this->info('4️⃣ Sample client data:');
                        $this->info("   Partner Account: {$sampleClient['partner_account']}");
                        $this->info("   Client UID: {$sampleClient['client_uid']}");
                        $this->info("   Volume Lots: {$sampleClient['volume_lots']}");
                        $this->info("   Volume USD: {$sampleClient['volume_mln_usd']}");
                        $this->info("   Reward USD: {$sampleClient['reward_usd']}");
                    }
                } else {
                    $this->error('❌ No clients data found in response');
                }
            } else {
                $this->error('❌ Controller did not return Inertia Response');
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            Log::error('TestClientAccount1Data error: ' . $e->getMessage());
        }
        
        $this->info('🔍 Test Complete!');
    }
} 