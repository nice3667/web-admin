<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class SyncAllUsersData extends Command
{
    protected $signature = 'sync:all-users {--force : Force sync even if recently synced}';
    protected $description = 'Sync all users data (Ham, Kantapong, Janischa) from Exness API';

    public function handle()
    {
        $this->info('Starting sync for all users...');
        $startTime = microtime(true);
        
        $results = [];
        $forceFlag = $this->option('force') ? ['--force' => true] : [];

        // Sync Ham data
        $this->info('1/3 Syncing Ham data...');
        try {
            $exitCode = Artisan::call('sync:ham-data', $forceFlag);
            $results['ham'] = [
                'success' => $exitCode === 0,
                'output' => Artisan::output()
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

        // Sync Kantapong data
        $this->info('2/3 Syncing Kantapong data...');
        try {
            $exitCode = Artisan::call('sync:kantapong-data', $forceFlag);
            $results['kantapong'] = [
                'success' => $exitCode === 0,
                'output' => Artisan::output()
            ];
            
            if ($exitCode === 0) {
                $this->info('✓ Kantapong data sync completed successfully');
            } else {
                $this->error('✗ Kantapong data sync failed');
            }
        } catch (\Exception $e) {
            $results['kantapong'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
            $this->error('✗ Kantapong data sync error: ' . $e->getMessage());
        }

        // Sync Janischa data
        $this->info('3/3 Syncing Janischa data...');
        try {
            $exitCode = Artisan::call('sync:janischa-data', $forceFlag);
            $results['janischa'] = [
                'success' => $exitCode === 0,
                'output' => Artisan::output()
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
        
        $this->info("Successfully synced: {$successCount}/3 users");
        
        // Log summary
        Log::info('All users sync completed', [
            'duration' => $duration,
            'results' => $results,
            'success_count' => $successCount,
            'total_users' => 3
        ]);

        // Return appropriate exit code
        return $successCount === 3 ? 0 : 1;
    }
}