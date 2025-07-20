<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\HamExnessAuthService;

class TestHamApi extends Command
{
    protected $signature = 'test:ham-api';
    protected $description = 'Test Ham Exness API connection and data';

    public function handle()
    {
        $this->info('=== TESTING HAM EXNESS API ===');

        try {
            $service = new HamExnessAuthService();
            $data = $service->getClientsData();

            $this->info('API Response type: ' . gettype($data));
            $this->info('Has error: ' . (isset($data['error']) ? 'YES' : 'NO'));

            if (isset($data['data'])) {
                $this->info('Data count: ' . count($data['data']));
                $this->info('Sample data:');
                $this->table(
                    ['Partner Account', 'Client UID', 'Client Account', 'Country', 'Status', 'Volume Lots', 'Volume USD', 'Reward USD'],
                    array_map(function($item) {
                        return [
                            $item['partner_account'] ?? '-',
                            $item['client_uid'] ?? '-',
                            $item['client_account'] ?? '-',
                            $item['client_country'] ?? $item['country'] ?? '-',
                            $item['client_status'] ?? 'UNKNOWN',
                            $item['volume_lots'] ?? 0,
                            $item['volume_mln_usd'] ?? 0,
                            $item['reward_usd'] ?? 0,
                        ];
                    }, array_slice($data['data'], 0, 10))
                );
            } else {
                $this->error('No data found');
                $this->line(print_r($data, true));
            }

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->line('Trace: ' . $e->getTraceAsString());
        }
    }
}
