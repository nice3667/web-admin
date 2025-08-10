<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Ham Clients - Dashboard FX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .stat-card .card-body {
            padding: 1.5rem;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
        }
        .table-responsive {
            border-radius: 0.375rem;
        }
        .badge-success {
            background-color: #28a745;
        }
        .badge-danger {
            background-color: #dc3545;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0">
                        <i class="fas fa-bug text-primary me-2"></i>
                        Debug Ham Clients
                    </h1>
                    <div>
                        <a href="{{ route('debug.janischa-clients') }}" class="btn btn-outline-info btn-sm me-2">
                            <i class="fas fa-users me-1"></i>Janischa
                        </a>
                        <a href="{{ route('debug.xm-clients') }}" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-chart-line me-1"></i>XM
                        </a>
                    </div>
                </div>
                <p class="text-muted">ข้อมูลลูกค้าทั้งหมดของ Ham Exness</p>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <div class="stat-number">{{ number_format($data['total_clients']) }}</div>
                        <div>ลูกค้าทั้งหมด</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="fas fa-id-card fa-2x mb-2"></i>
                        <div class="stat-number">{{ number_format($data['unique_uids']['count']) }}</div>
                        <div>Unique UIDs</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-pie fa-2x mb-2"></i>
                        <div class="stat-number">{{ count($data['countries']) }}</div>
                        <div>ประเทศ</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="fas fa-sync fa-2x mb-2"></i>
                        <div class="stat-number">{{ $data['ham_page_clients']['count'] }}</div>
                        <div>Ham Page</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row">
            <!-- Left Column -->
            <div class="col-md-8">
                <!-- Database Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-database text-primary me-2"></i>
                            ข้อมูลฐานข้อมูล
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>ลูกค้าล่าสุด:</strong></p>
                                @if($data['latest_client'])
                                    <ul class="list-unstyled">
                                        <li><strong>Client ID:</strong> {{ $data['latest_client']['client_id'] }}</li>
                                        <li><strong>UID:</strong> {{ $data['latest_client']['client_uid'] }}</li>
                                        <li><strong>วันที่ลงทะเบียน:</strong> {{ $data['latest_client']['reg_date'] }}</li>
                                    </ul>
                                @else
                                    <p class="text-muted">ไม่มีข้อมูล</p>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <p><strong>ลูกค้าเก่าสุด:</strong></p>
                                @if($data['oldest_client'])
                                    <ul class="list-unstyled">
                                        <li><strong>Client ID:</strong> {{ $data['oldest_client']['client_id'] }}</li>
                                        <li><strong>UID:</strong> {{ $data['oldest_client']['client_uid'] }}</li>
                                        <li><strong>วันที่ลงทะเบียน:</strong> {{ $data['oldest_client']['reg_date'] }}</li>
                                    </ul>
                                @else
                                    <p class="text-muted">ไม่มีข้อมูล</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ham Page Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-file-alt text-success me-2"></i>
                            ข้อมูลหน้า Ham (admin/reports1/client-account1)
                        </h5>
                    </div>
                    <div class="card-body">
                        <p><strong>จำนวนลูกค้าในหน้า Ham:</strong> {{ number_format($data['ham_page_clients']['count']) }}</p>
                        @if($data['ham_page_clients']['latest'])
                            <p><strong>ลูกค้าล่าสุดในหน้า Ham:</strong> {{ $data['ham_page_clients']['latest']['client_id'] }}</p>
                            <p><strong>วันที่ลงทะเบียนล่าสุด:</strong> {{ $data['ham_page_clients']['latest']['reg_date'] }}</p>
                        @endif
                    </div>
                </div>

                <!-- Volume & Reward Statistics -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar text-warning me-2"></i>
                            สถิติ Volume และ Reward
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Volume (Lots)</h6>
                                <p><strong>รวม:</strong> {{ $data['volume_stats']['total_volume'] }}</p>
                                <p><strong>เฉลี่ย:</strong> {{ $data['volume_stats']['avg_volume'] }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Reward (USD)</h6>
                                <p><strong>รวม:</strong> {{ $data['volume_stats']['total_reward'] }}</p>
                                <p><strong>เฉลี่ย:</strong> {{ $data['volume_stats']['avg_reward'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Last Sync Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-clock text-info me-2"></i>
                            ข้อมูลการ Sync ล่าสุด
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($data['last_sync'])
                            <p><strong>Last Sync:</strong> {{ $data['last_sync']['updated_at'] }}</p>
                            <p><strong>Client:</strong> {{ $data['last_sync']['client_id'] }}</p>
                        @else
                            <p class="text-muted">ไม่มีข้อมูลการ sync</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-md-4">
                <!-- Service Status -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-cog text-secondary me-2"></i>
                            สถานะ Service
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($data['service']['exists'])
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge badge-success me-2">✓</span>
                                <span>Service มีอยู่</span>
                            </div>
                            @if($data['service']['ready'])
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge badge-success me-2">✓</span>
                                    <span>พร้อมใช้งาน</span>
                                </div>
                                <div class="mt-3">
                                    <h6>Methods ที่มี:</h6>
                                    <ul class="list-unstyled">
                                        @foreach($data['service']['methods'] as $method => $exists)
                                            <li>
                                                <span class="badge {{ $exists ? 'badge-success' : 'badge-danger' }} me-2">
                                                    {{ $exists ? '✓' : '✗' }}
                                                </span>
                                                {{ $method }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @else
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge badge-danger me-2">✗</span>
                                    <span>ไม่พร้อมใช้งาน</span>
                                </div>
                                <p class="text-danger small">{{ $data['service']['error'] }}</p>
                            @endif
                        @else
                            <div class="d-flex align-items-center">
                                <span class="badge badge-danger me-2">✗</span>
                                <span>Service ไม่มีอยู่</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Countries -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-globe text-primary me-2"></i>
                            ประเทศ (Top 10)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>ประเทศ</th>
                                        <th class="text-end">จำนวน</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(array_slice($data['countries'], 0, 10) as $country)
                                        <tr>
                                            <td>{{ $country['country'] }}</td>
                                            <td class="text-end">{{ number_format($country['count']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Monthly Statistics -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar text-success me-2"></i>
                            สถิติรายเดือน (6 เดือนล่าสุด)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>เดือน</th>
                                        <th class="text-end">จำนวน</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(array_slice($data['monthly_stats'], 0, 6) as $month)
                                        <tr>
                                            <td>{{ $month['month'] }}</td>
                                            <td class="text-end">{{ number_format($month['count']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Status Statistics -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle text-warning me-2"></i>
                            สถิติสถานะ
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>สถานะ</th>
                                        <th class="text-end">จำนวน</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data['status_stats'] as $status)
                                        <tr>
                                            <td>{{ $status['status'] }}</td>
                                            <td class="text-end">{{ number_format($status['count']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="text-center text-muted">
                    <small>
                        <i class="fas fa-clock me-1"></i>
                        อัพเดทล่าสุด: {{ now()->format('Y-m-d H:i:s') }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
