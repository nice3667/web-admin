<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class SyncAllUsersData extends Command
{
    protected $signature = 'sync:all-users {--force : Force sync even if recently synced}';
    protected $description = 'Sync all users data (Ham, Janischa) from Exness API';

    public function handle()
    {
        $startTime = microtime(true);
        $this->info('🔄 Starting sync for all users...');
        
        $forceFlag = $this->option('force') ? ['--force' => true] : [];
        $results = [];

        // Sync Ham data
        try {
            $this->info('1/2 Syncing Ham data...');
            $exitCode = Artisan::call('sync:ham-data', $forceFlag);
            $results['ham'] = [
                'success' => $exitCode === 0,
                'exit_code' => $exitCode
            ];
            
            if ($exitCode === 0) {
                $this->info('✓ Ham data sync completed successfully');
            } else {
                $this->error('✗ Ham data sync failed');
            }
        } catch (\Exception $e) {
            $results['ham'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
            $this->error('✗ Ham data sync error: ' . $e->getMessage());
        }

        // Sync Janischa data
        try {
            $this->info('2/2 Syncing Janischa data...');
            $exitCode = Artisan::call('sync:janischa-data', $forceFlag);
            $results['janischa'] = [
                'success' => $exitCode === 0,
                'exit_code' => $exitCode
            ];
            
            if ($exitCode === 0) {
                $this->info('✓ Janischa data sync completed successfully');
            } else {
                $this->error('✗ Janischa data sync failed');
            }
        } catch (\Exception $e) {
            $results['janischa'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
            $this->error('✗ Janischa data sync error: ' . $e->getMessage());
        }

        // Summary
        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);
        
        $this->info('');
        $this->info('=== SYNC SUMMARY ===');
        $this->info("Duration: {$duration} seconds");
        
        $successCount = 0;
        foreach ($results as $user => $result) {
            $status = $result['success'] ? '✓ SUCCESS' : '✗ FAILED';
            $this->info(ucfirst($user) . ': ' . $status);
            if ($result['success']) {
                $successCount++;
            }
        }
        
        $this->info("Successfully synced: {$successCount}/2 users");
        
        // Log summary
        Log::info('All users sync completed', [
            'duration' => $duration,
            'results' => $results,
            'success_count' => $successCount,
            'total_users' => 2
        ]);

        // Return appropriate exit code
        return $successCount === 2 ? 0 : 1;
    }
}