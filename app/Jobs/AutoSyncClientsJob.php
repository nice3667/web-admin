<?php

namespace App\Jobs;

use App\Services\ClientService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AutoSyncClientsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes
    public $tries = 3;
    public $backoff = 60; // 1 minute between retries

    protected $newOnly;
    protected $interval;

    /**
     * Create a new job instance.
     */
    public function __construct(bool $newOnly = true, int $interval = 30)
    {
        $this->newOnly = $newOnly;
        $this->interval = $interval;
    }

    /**
     * Execute the job.
     */
    public function handle(ClientService $clientService): void
    {
        $startTime = microtime(true);
        
        Log::info('AutoSyncClientsJob started', [
            'new_only' => $this->newOnly,
            'interval' => $this->interval,
            'job_id' => $this->job->getJobId()
        ]);

        try {
            if ($this->newOnly) {
                $result = $clientService->syncNewClients();
                $success = $result['success'] ?? false;
                
                if ($success) {
                    $newClients = $result['new_clients_added'] ?? 0;
                    Log::info('AutoSyncClientsJob completed (new clients)', [
                        'new_clients_added' => $newClients,
                        'total_api_clients' => $result['total_api_clients'] ?? 0,
                        'existing_clients' => $result['existing_clients'] ?? 0
                    ]);

                    // Add to sync history
                    $this->addToSyncHistory('new_clients', $result);
                } else {
                    Log::error('AutoSyncClientsJob failed (new clients)', [
                        'error' => $result['error'] ?? 'Unknown error'
                    ]);
                    throw new \Exception($result['error'] ?? 'New clients sync failed');
                }
            } else {
                $success = $clientService->syncClients();
                
                if ($success) {
                    Log::info('AutoSyncClientsJob completed (full sync)');
                    $this->addToSyncHistory('full_sync', ['success' => true]);
                } else {
                    Log::error('AutoSyncClientsJob failed (full sync)');
                    throw new \Exception('Full sync failed');
                }
            }

            // Update last sync time
            Cache::put('last_client_sync', now(), now()->addDays(1));
            
            // Schedule next job if monitoring is still active
            $this->scheduleNextJob();

            $duration = round(microtime(true) - $startTime, 2);
            Log::info('AutoSyncClientsJob finished', [
                'duration' => $duration,
                'success' => true
            ]);

        } catch (\Exception $e) {
            Log::error('AutoSyncClientsJob error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'job_id' => $this->job->getJobId()
            ]);

            $this->addToSyncHistory('error', [
                'error' => $e->getMessage(),
                'job_id' => $this->job->getJobId()
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('AutoSyncClientsJob failed permanently', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'job_id' => $this->job->getJobId()
        ]);

        // Add to sync history
        $this->addToSyncHistory('failed', [
            'error' => $exception->getMessage(),
            'job_id' => $this->job->getJobId()
        ]);

        // Optionally send notification
        $this->sendFailureNotification($exception);
    }

    /**
     * Schedule the next job
     */
    protected function scheduleNextJob(): void
    {
        // Check if monitoring is still active
        $settings = Cache::get('realtime_sync_settings', []);
        
        if (($settings['is_active'] ?? false) && ($settings['interval'] ?? 0) > 0) {
            $nextRun = now()->addMinutes($settings['interval']);
            
            // Schedule next job
            self::dispatch($settings['new_only'] ?? true, $settings['interval'] ?? 30)
                ->delay($nextRun);
            
            Log::info('Next AutoSyncClientsJob scheduled', [
                'next_run' => $nextRun->toISOString(),
                'interval' => $settings['interval']
            ]);
        } else {
            Log::info('AutoSyncClientsJob monitoring stopped - no next job scheduled');
        }
    }

    /**
     * Add entry to sync history
     */
    protected function addToSyncHistory(string $type, array $data): void
    {
        $history = Cache::get('sync_history', []);
        
        $entry = [
            'type' => $type,
            'timestamp' => now()->toISOString(),
            'job_id' => $this->job->getJobId(),
            'data' => $data
        ];
        
        $history[] = $entry;
        
        // Keep only last 100 entries
        if (count($history) > 100) {
            $history = array_slice($history, -100);
        }
        
        Cache::put('sync_history', $history, now()->addDays(7));
    }

    /**
     * Send failure notification
     */
    protected function sendFailureNotification(\Throwable $exception): void
    {
        // This could send email, Slack notification, etc.
        Log::warning('AutoSyncClientsJob failure notification should be sent', [
            'error' => $exception->getMessage(),
            'job_id' => $this->job->getJobId()
        ]);
        
        // Example: Send to admin
        // Mail::to('admin@example.com')->send(new SyncFailureMail($exception));
    }
} 