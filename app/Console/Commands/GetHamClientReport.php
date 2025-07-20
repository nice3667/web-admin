<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\HamExnessAuthService;
use App\Models\Client;

class GetHamClientReport extends Command
{
    protected $signature = 'ham:client-report';
    protected $description = 'Get detailed Ham client account report';

    public function handle()
    {
        $this->info('=== HAM CLIENT ACCOUNT REPORT ===');
        $this->info('Email: hamsftmo@gmail.com');
        $this->info('Date: ' . now()->format('Y-m-d H:i:s'));
        $this->line('');

        // Database Data
        $this->info('📊 DATABASE DATA:');
        $totalDbClients = Client::count();
        $this->info("Total clients in database: {$totalDbClients}");

        $dbClients = Client::take(5)->get();
        if ($dbClients->count() > 0) {
            $this->info('Sample database clients:');
            $this->table(
                ['ID', 'Partner Account', 'Client UID', 'Client ID', 'Country', 'Status', 'Volume Lots', 'Volume USD', 'Reward USD'],
                $dbClients->map(function($client) {
                    return [
                        $client->id,
                        $client->partner_account,
                        $client->client_uid,
                        $client->client_id,
                        $client->client_country,
                        $client->client_status,
                        $client->volume_lots,
                        $client->volume_mln_usd,
                        $client->reward_usd,
                    ];
                })->toArray()
            );
        }
        $this->line('');

        // API Data
        $this->info('🌐 API DATA:');
        try {
            $service = new HamExnessAuthService();
            $data = $service->getClientsData();

            if (isset($data['data'])) {
                $apiClients = collect($data['data']);
                $this->info("Total API clients: {$apiClients->count()}");

                // Statistics
                $totalVolumeLots = $apiClients->sum('volume_lots');
                $totalVolumeUsd = $apiClients->sum('volume_mln_usd');
                $totalRewardUsd = $apiClients->sum('reward_usd');
                $uniqueCountries = $apiClients->pluck('client_country')->unique()->count();
                $thaiClients = $apiClients->where('client_country', 'TH')->count();

                $this->info("📈 STATISTICS:");
                $this->info("Total Volume (Lots): {$totalVolumeLots}");
                $this->info("Total Volume (USD): {$totalVolumeUsd}");
                $this->info("Total Reward (USD): {$totalRewardUsd}");
                $this->info("Unique Countries: {$uniqueCountries}");
                $this->info("Thai Clients: {$thaiClients}");

                // Countries breakdown
                $this->line('');
                $this->info("🌍 COUNTRIES BREAKDOWN:");
                $countries = $apiClients->groupBy('client_country')
                    ->map(function($group) {
                        return $group->count();
                    })
                    ->sortDesc();

                $this->table(
                    ['Country', 'Client Count'],
                    $countries->map(function($count, $country) {
                        return [$country, $count];
                    })->toArray()
                );

                // Top clients by volume
                $this->line('');
                $this->info("🏆 TOP 10 CLIENTS BY VOLUME:");
                $topClients = $apiClients->sortByDesc('volume_lots')->take(10);
                $this->table(
                    ['Client UID', 'Client Account', 'Country', 'Volume Lots', 'Volume USD', 'Reward USD'],
                    $topClients->map(function($client) {
                        return [
                            $client['client_uid'],
                            $client['client_account'],
                            $client['client_country'] ?? $client['country'] ?? '-',
                            $client['volume_lots'],
                            $client['volume_mln_usd'],
                            $client['reward_usd'],
                        ];
                    })->toArray()
                );

                // Top clients by reward
                $this->line('');
                $this->info("💰 TOP 10 CLIENTS BY REWARD:");
                $topRewardClients = $apiClients->sortByDesc('reward_usd')->take(10);
                $this->table(
                    ['Client UID', 'Client Account', 'Country', 'Volume Lots', 'Volume USD', 'Reward USD'],
                    $topRewardClients->map(function($client) {
                        return [
                            $client['client_uid'],
                            $client['client_account'],
                            $client['client_country'] ?? $client['country'] ?? '-',
                            $client['volume_lots'],
                            $client['volume_mln_usd'],
                            $client['reward_usd'],
                        ];
                    })->toArray()
                );

            } else {
                $this->error('No API data found');
                $this->line(print_r($data, true));
            }

        } catch (\Exception $e) {
            $this->error('API Error: ' . $e->getMessage());
        }

        $this->line('');
        $this->info('=== END OF REPORT ===');
    }
}
