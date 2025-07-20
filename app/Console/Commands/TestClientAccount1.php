<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Admin\Report1Controller;
use App\Services\HamExnessAuthService;
use App\Models\HamClient;
use Illuminate\Http\Request;

class TestClientAccount1 extends Command
{
    protected $signature = 'test:client-account1';
    protected $description = 'Test ClientAccount1 data to debug display issues';

    public function handle()
    {
        $this->info('🧪 Testing ClientAccount1 Data');
        $this->info('=============================');

        // Test 1: Ham Service
        $this->info('1️⃣ Testing HamExnessAuthService...');
        try {
            $hamService = new HamExnessAuthService();
            $apiResponse = $hamService->getClientsData();
            
            if (isset($apiResponse['error'])) {
                $this->error("❌ API Error: " . $apiResponse['error']);
            } else {
                $apiCount = isset($apiResponse['data']) ? count($apiResponse['data']) : 0;
                $this->info("✅ API Success: {$apiCount} records");
                
                if ($apiCount > 0) {
                    $sample = $apiResponse['data'][0];
                    $this->info("Sample API data keys: " . implode(', ', array_keys($sample)));
                }
            }
        } catch (\Exception $e) {
            $this->error("❌ API Exception: " . $e->getMessage());
        }

        // Test 2: Database
        $this->info('');
        $this->info('2️⃣ Testing Database...');
        try {
            $dbCount = HamClient::count();
            $this->info("✅ Database: {$dbCount} records");
            
            if ($dbCount > 0) {
                $sample = HamClient::first();
                $this->info("Sample DB data keys: " . implode(', ', array_keys($sample->toArray())));
            }
        } catch (\Exception $e) {
            $this->error("❌ Database Exception: " . $e->getMessage());
        }

        // Test 3: Controller Method
        $this->info('');
        $this->info('3️⃣ Testing Controller Method...');
        try {
            $controller = new Report1Controller(new HamExnessAuthService());
            $request = new Request();
            
            // จำลอง request
            $request->merge([
                'page' => 1
            ]);
            
            $response = $controller->clientAccount1($request);
            
            if ($response instanceof \Inertia\Response) {
                $this->info("✅ Controller returned Inertia Response");
                
                // ใช้ reflection เพื่อเข้าถึงข้อมูล
                $reflection = new \ReflectionClass($response);
                $propsProperty = $reflection->getProperty('props');
                $propsProperty->setAccessible(true);
                $props = $propsProperty->getValue($response);
                
                $this->info("Response props keys: " . implode(', ', array_keys($props)));
                
                if (isset($props['clients'])) {
                    $clients = $props['clients'];
                    if (is_array($clients) && isset($clients['data'])) {
                        $this->info("Clients data count: " . count($clients['data']));
                        $this->info("Total clients: " . ($clients['total'] ?? 'unknown'));
                    } else {
                        $this->warn("⚠️ Clients data structure unexpected");
                        $this->info("Clients type: " . gettype($clients));
                    }
                }
                
                if (isset($props['stats'])) {
                    $stats = $props['stats'];
                    $this->info("Stats: " . json_encode($stats, JSON_PRETTY_PRINT));
                }
                
                if (isset($props['error'])) {
                    $this->error("Controller Error: " . $props['error']);
                }
                
                $this->info("Data source: " . ($props['data_source'] ?? 'unknown'));
                $this->info("User email: " . ($props['user_email'] ?? 'unknown'));
                
            } else {
                $this->error("❌ Controller returned unexpected response type: " . get_class($response));
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Controller Exception: " . $e->getMessage());
            $this->error("Trace: " . $e->getTraceAsString());
        }

        // Test 4: Check Vue Component
        $this->info('');
        $this->info('4️⃣ Checking Vue Component...');
        $vuePath = resource_path('js/Pages/Admin/Report1/ClientAccount1.vue');
        
        if (file_exists($vuePath)) {
            $this->info("✅ Vue component exists at: {$vuePath}");
            $content = file_get_contents($vuePath);
            
            // Check for common patterns
            if (strpos($content, 'ไม่พบข้อมูล') !== false) {
                $this->warn("⚠️ Found 'ไม่พบข้อมูล' in Vue component");
            }
            
            if (strpos($content, 'clients.data') !== false) {
                $this->info("✅ Component references clients.data");
            }
            
            if (strpos($content, 'v-if') !== false) {
                $this->info("✅ Component has conditional rendering");
            }
            
        } else {
            $this->error("❌ Vue component not found at: {$vuePath}");
        }

        $this->info('');
        $this->info('🔍 Diagnosis Complete!');
        
        return 0;
    }
} 