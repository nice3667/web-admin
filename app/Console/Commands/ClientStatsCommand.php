<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ClientService;
use Illuminate\Support\Facades\Cache;

class ClientStatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clients:stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show client sync statistics';

    protected $clientService;

    public function __construct(ClientService $clientService)
    {
        parent::__construct();
        $this->clientService = $clientService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📊 Client Sync Statistics');
        $this->info('========================');

        try {
            $stats = $this->clientService->getSyncStats();
            
            $this->info('📈 Database Statistics:');
            $this->info('- Total Clients: ' . ($stats['total_clients'] ?? 0));
            $this->info('- Last Sync: ' . ($stats['last_sync'] ?? 'Never'));
            $this->info('- Synced Today: ' . ($stats['clients_synced_today'] ?? 0));
            $this->info('- Synced This Week: ' . ($stats['clients_synced_this_week'] ?? 0));

            if (!empty($stats['status_distribution'])) {
                $this->info('');
                $this->info('📋 Status Distribution:');
                foreach ($stats['status_distribution'] as $status => $count) {
                    $this->info("  • {$status}: {$count}");
                }
            }

            if (!empty($stats['countries_distribution'])) {
                $this->info('');
                $this->info('🌍 Top Countries:');
                foreach ($stats['countries_distribution'] as $country => $count) {
                    $this->info("  • {$country}: {$count}");
                }
            }

            // Check auto sync status
            $this->info('');
            $this->info('🤖 Auto Sync Status:');
            
            $autoSyncSettings = Cache::get('realtime_sync_settings', []);
            if (!empty($autoSyncSettings) && ($autoSyncSettings['is_active'] ?? false)) {
                $this->info('✅ Auto sync is ACTIVE');
                $this->info('- Interval: ' . ($autoSyncSettings['interval'] ?? 'N/A') . ' minutes');
                $this->info('- New Only: ' . (($autoSyncSettings['new_only'] ?? true) ? 'Yes' : 'No'));
                $this->info('- Started: ' . ($autoSyncSettings['started_at'] ?? 'N/A'));
            } else {
                $this->info('❌ Auto sync is INACTIVE');
                $this->info('💡 To start auto sync, run:');
                $this->info('   php artisan clients:auto-sync --daemon --interval=15 --new-only');
            }

            // Check last sync time
            $lastSync = Cache::get('last_client_sync');
            if ($lastSync) {
                $this->info('');
                $this->info('🕒 Last Sync Time: ' . $lastSync->format('Y-m-d H:i:s'));
                $this->info('⏱️  Time Since Last Sync: ' . $lastSync->diffForHumans());
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error getting statistics: ' . $e->getMessage());
            return 1;
        }
    }
} 