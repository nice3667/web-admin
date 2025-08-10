<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class DebugController extends Controller
{
    /**
     * Debug ข้อมูลลูกค้าทั้งหมดของ Ham
     */
    public function debugHamClients()
    {
        try {
            $data = [];
            
            // 1. Database
            $dbClients = DB::table('clients')
                ->orderBy('reg_date', 'desc')
                ->get();

            $data['total_clients'] = $dbClients->count();
            
            if ($dbClients->count() > 0) {
                $data['latest_client'] = [
                    'client_id' => $dbClients->first()->client_id,
                    'client_uid' => $dbClients->first()->client_uid,
                    'reg_date' => $dbClients->first()->reg_date
                ];
                
                $data['oldest_client'] = [
                    'client_id' => $dbClients->last()->client_id,
                    'client_uid' => $dbClients->last()->client_uid,
                    'reg_date' => $dbClients->last()->reg_date
                ];
            }

            // 2. Ham Page (admin/reports1/client-account1)
            $clientAccount1Clients = DB::table('clients')
                ->where('partner_account', 'like', '%1172984151037556173%')
                ->orderBy('reg_date', 'desc')
                ->get();

            $data['ham_page_clients'] = [
                'count' => $clientAccount1Clients->count(),
                'latest' => $clientAccount1Clients->first() ? [
                    'client_id' => $clientAccount1Clients->first()->client_id,
                    'reg_date' => $clientAccount1Clients->first()->reg_date
                ] : null
            ];

            // 3. Unique UIDs
            $uniqueUids = DB::table('clients')
                ->distinct()
                ->pluck('client_uid');

            $data['unique_uids'] = [
                'count' => $uniqueUids->count(),
                'latest' => $uniqueUids->first(),
                'oldest' => $uniqueUids->last()
            ];

            // 4. Countries
            $countries = DB::table('clients')
                ->select('client_country', DB::raw('count(*) as count'))
                ->groupBy('client_country')
                ->orderBy('count', 'desc')
                ->get();

            $data['countries'] = $countries->map(function($country) {
                return [
                    'country' => $country->client_country ?? 'ไม่ระบุ',
                    'count' => $country->count
                ];
            });

            // 5. Monthly
            $monthlyStats = DB::table('clients')
                ->select(DB::raw('DATE_FORMAT(reg_date, "%Y-%m") as month'), DB::raw('count(*) as count'))
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->get();

            $data['monthly_stats'] = $monthlyStats->map(function($month) {
                return [
                    'month' => $month->month,
                    'count' => $month->count
                ];
            });

            // 6. Volume & Reward
            $volumeStats = DB::table('clients')
                ->select(
                    DB::raw('SUM(volume_lots) as total_volume'),
                    DB::raw('SUM(reward_usd) as total_reward'),
                    DB::raw('AVG(volume_lots) as avg_volume'),
                    DB::raw('AVG(reward_usd) as avg_reward')
                )
                ->first();

            $data['volume_stats'] = [
                'total_volume' => number_format((float)($volumeStats->total_volume ?? 0), 2),
                'total_reward' => number_format((float)($volumeStats->total_reward ?? 0), 2),
                'avg_volume' => number_format((float)($volumeStats->avg_volume ?? 0), 2),
                'avg_reward' => number_format((float)($volumeStats->avg_reward ?? 0), 2)
            ];

            // 7. Status
            $statusStats = DB::table('clients')
                ->select('client_status', DB::raw('count(*) as count'))
                ->groupBy('client_status')
                ->orderBy('count', 'desc')
                ->get();

            $data['status_stats'] = $statusStats->map(function($status) {
                return [
                    'status' => $status->client_status ?? 'ไม่ระบุ',
                    'count' => $status->count
                ];
            });

            // 8. Last Sync
            $lastSync = DB::table('clients')
                ->orderBy('updated_at', 'desc')
                ->first();

            $data['last_sync'] = $lastSync ? [
                'updated_at' => $lastSync->updated_at,
                'client_id' => $lastSync->client_id
            ] : null;

            // 9. Service
            if (class_exists('App\Services\HamExnessAuthService')) {
                try {
                    $hamService = new \App\Services\HamExnessAuthService();
                    $data['service'] = [
                        'exists' => true,
                        'ready' => true,
                        'methods' => [
                            'getClients' => method_exists($hamService, 'getClients'),
                            'getClientCount' => method_exists($hamService, 'getClientCount')
                        ]
                    ];
                } catch (\Throwable $e) {
                    $data['service'] = [
                        'exists' => true,
                        'ready' => false,
                        'error' => $e->getMessage()
                    ];
                }
            } else {
                $data['service'] = [
                    'exists' => false,
                    'ready' => false
                ];
            }

            return view('debug.ham_clients', compact('data'));
            
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Debug ข้อมูลลูกค้าทั้งหมดของ Janischa
     */
    public function debugJanischaClients()
    {
        try {
            $data = [];
            
            // 1. Database
            $dbClients = DB::table('clients')
                ->orderBy('reg_date', 'desc')
                ->get();

            $data['total_clients'] = $dbClients->count();
            
            if ($dbClients->count() > 0) {
                $data['latest_client'] = [
                    'client_id' => $dbClients->first()->client_id,
                    'client_uid' => $dbClients->first()->client_uid,
                    'reg_date' => $dbClients->first()->reg_date
                ];
                
                $data['oldest_client'] = [
                    'client_id' => $dbClients->last()->client_id,
                    'client_uid' => $dbClients->last()->client_uid,
                    'reg_date' => $dbClients->last()->reg_date
                ];
            }

            // 2. Countries
            $countries = DB::table('clients')
                ->select('client_country', DB::raw('count(*) as count'))
                ->groupBy('client_country')
                ->orderBy('count', 'desc')
                ->get();

            $data['countries'] = $countries->map(function($country) {
                return [
                    'country' => $country->client_country ?? 'ไม่ระบุ',
                    'count' => $country->count
                ];
            });

            // 3. Monthly
            $monthlyStats = DB::table('clients')
                ->select(DB::raw('DATE_FORMAT(reg_date, "%Y-%m") as month'), DB::raw('count(*) as count'))
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->get();

            $data['monthly_stats'] = $monthlyStats->map(function($month) {
                return [
                    'month' => $month->month,
                    'count' => $month->count
                ];
            });

            // 4. Volume & Reward
            $volumeStats = DB::table('clients')
                ->select(
                    DB::raw('SUM(volume_lots) as total_volume'),
                    DB::raw('SUM(reward_usd) as total_reward'),
                    DB::raw('AVG(volume_lots) as avg_volume'),
                    DB::raw('AVG(reward_usd) as avg_reward')
                )
                ->first();

            $data['volume_stats'] = [
                'total_volume' => number_format((float)($volumeStats->total_volume ?? 0), 2),
                'total_reward' => number_format((float)($volumeStats->total_reward ?? 0), 2),
                'avg_volume' => number_format((float)($volumeStats->avg_volume ?? 0), 2),
                'avg_reward' => number_format((float)($volumeStats->avg_reward ?? 0), 2)
            ];

            // 5. Status
            $statusStats = DB::table('clients')
                ->select('client_status', DB::raw('count(*) as count'))
                ->groupBy('client_status')
                ->orderBy('count', 'desc')
                ->get();

            $data['status_stats'] = $statusStats->map(function($status) {
                return [
                    'status' => $status->client_status ?? 'ไม่ระบุ',
                    'count' => $status->count
                ];
            });

            // 6. Service
            if (class_exists('App\Services\JanischaExnessAuthService')) {
                try {
                    $janischaService = new \App\Services\JanischaExnessAuthService();
                    $data['service'] = [
                        'exists' => true,
                        'ready' => true,
                        'methods' => [
                            'getClients' => method_exists($janischaService, 'getClients'),
                            'getClientCount' => method_exists($janischaService, 'getClientCount')
                        ]
                    ];
                } catch (\Throwable $e) {
                    $data['service'] = [
                        'exists' => true,
                        'ready' => false,
                        'error' => $e->getMessage()
                    ];
                }
            } else {
                $data['service'] = [
                    'exists' => false,
                    'ready' => false
                ];
            }

            return view('debug.janischa_clients', compact('data'));
            
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Debug ข้อมูลลูกค้าทั้งหมดของ XM
     */
    public function debugXmClients()
    {
        try {
            $data = [];
            
            // 1. Database
            $dbClients = DB::table('clients')
                ->orderBy('reg_date', 'desc')
                ->get();

            $data['total_clients'] = $dbClients->count();
            
            if ($dbClients->count() > 0) {
                $data['latest_client'] = [
                    'client_id' => $dbClients->first()->client_id,
                    'client_uid' => $dbClients->first()->client_uid,
                    'reg_date' => $dbClients->first()->reg_date
                ];
                
                $data['oldest_client'] = [
                    'client_id' => $dbClients->last()->client_id,
                    'client_uid' => $dbClients->last()->client_uid,
                    'reg_date' => $dbClients->last()->reg_date
                ];
            }

            // 2. XM Page
            $xmPageClients = DB::table('clients')
                ->where('partner_account', 'like', '%XM%')
                ->orderBy('reg_date', 'desc')
                ->get();

            $data['xm_page_clients'] = [
                'count' => $xmPageClients->count(),
                'latest' => $xmPageClients->first() ? [
                    'client_id' => $xmPageClients->first()->client_id,
                    'reg_date' => $xmPageClients->first()->reg_date
                ] : null
            ];

            // 3. Unique UIDs
            $uniqueUids = DB::table('clients')
                ->distinct()
                ->pluck('client_uid');

            $data['unique_uids'] = [
                'count' => $uniqueUids->count(),
                'latest' => $uniqueUids->first(),
                'oldest' => $uniqueUids->last()
            ];

            // 4. Countries
            $countries = DB::table('clients')
                ->select('client_country', DB::raw('count(*) as count'))
                ->groupBy('client_country')
                ->orderBy('count', 'desc')
                ->get();

            $data['countries'] = $countries->map(function($country) {
                return [
                    'country' => $country->client_country ?? 'ไม่ระบุ',
                    'count' => $country->count
                ];
            });

            // 5. Monthly
            $monthlyStats = DB::table('clients')
                ->select(DB::raw('DATE_FORMAT(reg_date, "%Y-%m") as month'), DB::raw('count(*) as count'))
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->get();

            $data['monthly_stats'] = $monthlyStats->map(function($month) {
                return [
                    'month' => $month->month,
                    'count' => $month->count
                ];
            });

            // 6. Volume & Reward
            $volumeStats = DB::table('clients')
                ->select(
                    DB::raw('SUM(volume_lots) as total_volume'),
                    DB::raw('SUM(reward_usd) as total_reward'),
                    DB::raw('AVG(volume_lots) as avg_volume'),
                    DB::raw('AVG(reward_usd) as avg_reward')
                )
                ->first();

            $data['volume_stats'] = [
                'total_volume' => number_format((float)($volumeStats->total_volume ?? 0), 2),
                'total_reward' => number_format((float)($volumeStats->total_reward ?? 0), 2),
                'avg_volume' => number_format((float)($volumeStats->avg_volume ?? 0), 2),
                'avg_reward' => number_format((float)($volumeStats->avg_reward ?? 0), 2)
            ];

            // 7. Status
            $statusStats = DB::table('clients')
                ->select('client_status', DB::raw('count(*) as count'))
                ->groupBy('client_status')
                ->orderBy('count', 'desc')
                ->get();

            $data['status_stats'] = $statusStats->map(function($status) {
                return [
                    'status' => $status->client_status ?? 'ไม่ระบุ',
                    'count' => $status->count
                ];
            });

            // 8. Last Sync
            $lastSync = DB::table('clients')
                ->orderBy('updated_at', 'desc')
                ->first();

            $data['last_sync'] = $lastSync ? [
                'updated_at' => $lastSync->updated_at,
                'client_id' => $lastSync->client_id
            ] : null;

            // 9. Service
            if (class_exists('App\Services\XmAuthService')) {
                try {
                    $xmService = new \App\Services\XmAuthService();
                    $data['service'] = [
                        'exists' => true,
                        'ready' => true,
                        'methods' => [
                            'getClients' => method_exists($xmService, 'getClients'),
                            'getClientCount' => method_exists($xmService, 'getClientCount')
                        ]
                    ];
                } catch (\Throwable $e) {
                    $data['service'] = [
                        'exists' => true,
                        'ready' => false,
                        'error' => $e->getMessage()
                    ];
                }
            } else {
                $data['service'] = [
                    'exists' => false,
                    'ready' => false
                ];
            }

            // 10. XM folder
            $xmFolderPath = base_path('XM');
            if (is_dir($xmFolderPath)) {
                $xmFiles = glob($xmFolderPath . '/*.php') ?: [];
                $data['xm_folder'] = [
                    'exists' => true,
                    'path' => $xmFolderPath,
                    'php_files_count' => count($xmFiles),
                    'php_files' => array_map('basename', $xmFiles)
                ];
            } else {
                $data['xm_folder'] = [
                    'exists' => false
                ];
            }

            // 11. Backups
            $backupFiles = glob(storage_path('backups/*.json')) ?: [];
            $xmBackups = array_filter($backupFiles, function ($file) {
                return strpos(basename($file), 'clients') !== false;
            });

            $data['backups'] = [
                'total_files' => count($backupFiles),
                'clients_files' => count($xmBackups),
                'latest' => count($xmBackups) > 0 ? [
                    'filename' => basename(end($xmBackups)),
                    'size_kb' => number_format(filesize(end($xmBackups)) / 1024, 2),
                    'modified' => date('Y-m-d H:i:s', filemtime(end($xmBackups)))
                ] : null
            ];

            return view('debug.xm_clients', compact('data'));
            
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
