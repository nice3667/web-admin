<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use Illuminate\Support\Facades\DB;

class ClientSummaryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clients:summary 
                            {--by-uid : Group by Client UID}
                            {--show-accounts : Show individual accounts}
                            {--limit=10 : Number of UIDs to show}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show client summary grouped by UID with multiple accounts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📊 Client Summary Report');
        $this->info('========================');

        if ($this->option('by-uid')) {
            $this->showSummaryByUid();
        } else {
            $this->showGeneralSummary();
        }

        return 0;
    }

    /**
     * Show general summary
     */
    private function showGeneralSummary()
    {
        $this->info('📈 General Statistics:');
        
        $totalRecords = Client::count();
        $uniqueUids = Client::distinct('client_uid')->count();
        $totalVolumeLots = Client::sum('volume_lots');
        $totalVolumeUsd = Client::sum('volume_mln_usd');
        $totalReward = Client::sum('reward_usd');
        $totalRebate = Client::sum('rebate_amount_usd');
        
        $this->line("   • Total Records: {$totalRecords}");
        $this->line("   • Unique Client UIDs: {$uniqueUids}");
        $this->line("   • Average Accounts per UID: " . number_format($totalRecords / $uniqueUids, 2));
        $this->line("   • Total Volume (Lots): " . number_format($totalVolumeLots, 2));
        $this->line("   • Total Volume (USD): " . number_format($totalVolumeUsd, 2));
        $this->line("   • Total Reward (USD): " . number_format($totalReward, 2));
        $this->line("   • Total Rebate (USD): " . number_format($totalRebate, 2));
        
        // Status distribution
        $statusCounts = Client::selectRaw('client_status, COUNT(*) as count')
            ->groupBy('client_status')
            ->get()
            ->pluck('count', 'client_status')
            ->toArray();
        
        $this->line("   • Status Distribution:");
        foreach ($statusCounts as $status => $count) {
            $this->line("     - {$status}: {$count}");
        }
        
        $this->line('');
    }

    /**
     * Show summary grouped by UID
     */
    private function showSummaryByUid()
    {
        $limit = (int) $this->option('limit');
        $showAccounts = $this->option('show-accounts');
        
        $this->info("📋 Client Summary by UID (showing {$limit} UIDs):");
        $this->line(str_repeat('-', 120));
        $this->line(sprintf('%-15s %-8s %-15s %-15s %-15s %-15s %-10s', 'Client UID', 'Accounts', 'Total Lots', 'Total USD', 'Total Reward', 'Total Rebate', 'Status'));
        $this->line(str_repeat('-', 120));
        
        // Get UIDs with their aggregated data
        $uidSummaries = Client::selectRaw('
                client_uid,
                COUNT(*) as account_count,
                SUM(volume_lots) as total_volume_lots,
                SUM(volume_mln_usd) as total_volume_usd,
                SUM(reward_usd) as total_reward_usd,
                SUM(rebate_amount_usd) as total_rebate_usd,
                GROUP_CONCAT(DISTINCT client_status) as statuses
            ')
            ->groupBy('client_uid')
            ->orderBy('total_volume_lots', 'desc')
            ->limit($limit)
            ->get();
        
        foreach ($uidSummaries as $summary) {
            $statuses = explode(',', $summary->statuses);
            $mainStatus = count($statuses) > 1 ? 'MIXED' : $statuses[0];
            
            $this->line(sprintf(
                '%-15s %-8s %-15s %-15s %-15s %-15s %-10s',
                substr($summary->client_uid, 0, 12) . '...',
                $summary->account_count,
                number_format($summary->total_volume_lots, 2),
                number_format($summary->total_volume_usd, 2),
                number_format($summary->total_reward_usd, 2),
                number_format($summary->total_rebate_usd, 2),
                $mainStatus
            ));
            
            // Show individual accounts if requested
            if ($showAccounts && $summary->account_count > 1) {
                $accounts = Client::where('client_uid', $summary->client_uid)
                    ->select('client_id', 'client_status', 'volume_lots', 'volume_mln_usd', 'reward_usd', 'rebate_amount_usd')
                    ->get();
                
                foreach ($accounts as $index => $account) {
                    $this->line(sprintf(
                        '     %d. Account: %-10s | Status: %-10s | Lots: %-8s | USD: %-8s | Reward: %-8s | Rebate: %-8s',
                        $index + 1,
                        $account->client_id ?? 'N/A',
                        $account->client_status,
                        number_format($account->volume_lots, 2),
                        number_format($account->volume_mln_usd, 2),
                        number_format($account->reward_usd, 2),
                        number_format($account->rebate_amount_usd, 2)
                    ));
                }
                $this->line('');
            }
        }
        
        $this->line(str_repeat('-', 120));
        $this->line('');
        
        // Show top UIDs with multiple accounts
        $this->showTopMultipleAccounts();
    }

    /**
     * Show top UIDs with multiple accounts
     */
    private function showTopMultipleAccounts()
    {
        $this->info('🏆 Top UIDs with Multiple Accounts:');
        
        $multipleAccounts = Client::selectRaw('
                client_uid,
                COUNT(*) as account_count,
                SUM(volume_lots) as total_volume_lots,
                SUM(volume_mln_usd) as total_volume_usd,
                SUM(reward_usd) as total_reward_usd,
                SUM(rebate_amount_usd) as total_rebate_usd
            ')
            ->groupBy('client_uid')
            ->having('account_count', '>', 1)
            ->orderBy('account_count', 'desc')
            ->orderBy('total_volume_lots', 'desc')
            ->limit(10)
            ->get();
        
        if ($multipleAccounts->isEmpty()) {
            $this->line('   ✅ No UIDs with multiple accounts found');
            return;
        }
        
        $this->line(str_repeat('-', 100));
        $this->line(sprintf('%-15s %-8s %-15s %-15s %-15s %-15s', 'Client UID', 'Accounts', 'Total Lots', 'Total USD', 'Total Reward', 'Total Rebate'));
        $this->line(str_repeat('-', 100));
        
        foreach ($multipleAccounts as $uid) {
            $this->line(sprintf(
                '%-15s %-8s %-15s %-15s %-15s %-15s',
                substr($uid->client_uid, 0, 12) . '...',
                $uid->account_count,
                number_format($uid->total_volume_lots, 2),
                number_format($uid->total_volume_usd, 2),
                number_format($uid->total_reward_usd, 2),
                number_format($uid->total_rebate_usd, 2)
            ));
        }
        
        $this->line(str_repeat('-', 100));
        $this->line('');
    }
} 