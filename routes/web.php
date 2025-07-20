<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ExnessController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\RealtimeSyncController;
use App\Http\Controllers\Admin\CustomersController;


Route::get('/', function () {
    return redirect()->route('login');
});

// Debug route for session checking
Route::get('/debug/session', function () {
    return response()->json([
        'authenticated' => Auth::check(),
        'user_id' => Auth::id(),
        'user_email' => Auth::user()?->email,
        'session_id' => session()->getId(),
        'has_exness_token' => session()->has('exness_token'),
        'has_credentials' => session()->has('exness_credentials'),
        'token_length' => session()->has('exness_token') ? strlen(session('exness_token')) : 0,
        'session_data' => [
            'exness_token' => session()->has('exness_token') ? 'exists' : 'missing',
            'exness_credentials' => session()->has('exness_credentials') ? 'exists' : 'missing',
            'api_domain' => session('api_domain', 'not_set'),
            'token_created_at' => session('token_created_at', 'not_set')
        ]
    ]);
})->middleware(['auth']);

// Test Exness API with specific credentials
Route::get('/debug/test-exness/{email}/{password}', function ($email, $password) {
    try {
        $response = Http::timeout(30)->withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://my.exnessaffiliates.com/api/v2/auth/', [
            'login' => $email,
            'password' => $password
        ]);

        return response()->json([
            'email' => $email,
            'status' => $response->status(),
            'successful' => $response->successful(),
            'response' => $response->json(),
            'has_token' => isset($response->json()['token']),
            'token_length' => isset($response->json()['token']) ? strlen($response->json()['token']) : 0
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'email' => $email
        ]);
    }
})->middleware(['auth']);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return redirect('/admin/customers');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Exness Test Routes
    Route::get('/exness/test-token', function () {
        return Inertia::render('Admin/Exness/TestToken');
    })->name('exness.test-token');

    // Exness API routes
    Route::get('/api/exness/token', [ExnessController::class, 'getToken']);
    Route::get('/api/exness/clients', [ExnessController::class, 'getClients']);
    Route::get('/api/exness/wallets', [ExnessController::class, 'getWallets']);
    Route::get('/api/wallet/accounts', [ExnessController::class, 'getWalletAccounts']);
    Route::post('/api/exness/credentials', [ExnessController::class, 'saveCredentials']);

    // Debug route for API analysis
    Route::get('/api/exness/debug', [ExnessController::class, 'debugApiResponses']);
    Route::get('/api/exness/debug-db', [ExnessController::class, 'debugDatabaseStatus']);

    // Client sync route
    Route::post('/api/clients/sync', [ClientController::class, 'sync']);
    Route::post('/api/clients/sync-new', [ClientController::class, 'syncNewClients']);
    Route::get('/api/clients/sync-stats', [ClientController::class, 'syncStats']);
    Route::get('/api/clients/debug', [ClientController::class, 'debugApi']);
    Route::get('/api/clients/debug-db', [ClientController::class, 'debugDatabase']);

    // Real-time sync routes
    Route::prefix('api/realtime-sync')->group(function () {
        Route::post('/start', [RealtimeSyncController::class, 'startMonitoring']);
        Route::post('/stop', [RealtimeSyncController::class, 'stopMonitoring']);
        Route::get('/status', [RealtimeSyncController::class, 'getStatus']);
        Route::post('/trigger', [RealtimeSyncController::class, 'triggerSync']);
        Route::get('/history', [RealtimeSyncController::class, 'getSyncHistory']);
        Route::get('/websocket', [RealtimeSyncController::class, 'websocketEndpoint']);
    });

    // Exness credentials management
    Route::get('/exness/credentials', [ExnessController::class, 'credentials'])->name('exness.credentials');
    Route::post('/exness/credentials', [ExnessController::class, 'updateCredentials'])->name('exness.credentials.update');
});

// Admin Routes
Route::middleware(['web', 'auth', 'verified', \App\Http\Middleware\HasAccessAdmin::class, \App\Http\Middleware\Admin\HandleInertiaAdminRequests::class])->prefix('admin')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Admin/Dashboard');
    })->name('admin.dashboard');

    // Rebate Routes
    Route::get('/rebate', function () {
        return Inertia::render('Admin/Rebate/Index');
    })->name('admin.rebate');

    // Promo Routes
    Route::get('/promo', function () {
        return Inertia::render('Admin/Promo/Index');
    })->name('admin.promo');

    // Referral Routes
    Route::get('/referral', function () {
        return Inertia::render('Admin/Referral/Index');
    })->name('admin.referral');

    // Agent Routes
    Route::get('/agent', function () {
        return Inertia::render('Admin/Agent/Index');
    })->name('admin.agent');

    // Support Routes
    Route::get('/support', function () {
        return Inertia::render('Admin/Support/Index');
    })->name('admin.support');

    // Reports Routes
    Route::get('/reports/clients', [\App\Http\Controllers\Admin\ReportController::class, 'clients'])->name('admin.reports.clients');
Route::get('/reports/client-account', [\App\Http\Controllers\Admin\ReportController::class, 'clientAccount'])->name('admin.reports.client-account');

    Route::get('/reports/reward-history', function () {
        return Inertia::render('Admin/Report/RewardHistory');
    })->name('admin.reports.reward-history');

    Route::get('/reports/client-transaction', function () {
        return Inertia::render('Admin/Report/ClientTransaction');
    })->name('admin.reports.client-transaction');

    Route::get('/reports/transactions-pending', function () {
        return Inertia::render('Admin/Report/TransactionsPending');
    })->name('admin.reports.transactions-pending');

    Route::get('/all-customers', [CustomersController::class, 'allCustomers']);
});

// Route::get('/exness/test', [ExnessController::class, 'test']);
Route::get('/exness/test', [ExnessController::class, 'getClients']);
Route::get('/api/exness/clients', [ExnessController::class, 'getClients']);
// Legacy routes - kept for backward compatibility
Route::get('/api/exness/clients/v1', [ExnessController::class, 'getClients']);
Route::get('/api/exness/clients/v2', [ExnessController::class, 'getWallets']);

require __DIR__.'/auth.php';
