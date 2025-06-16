<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ExnessController;


Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('admin.dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Exness Test Routes
    Route::get('/exness/test-token', function () {
        return Inertia::render('Admin/Exness/TestToken');
    })->name('exness.test-token');

    // Add Exness API routes
    Route::get('/api/exness/token', [ExnessController::class, 'getToken']);
    Route::get('/api/exness/clients', [ExnessController::class, 'getClients']);
    Route::get('/api/exness/wallets', [ExnessController::class, 'getWallets']);
    Route::post('/api/exness/credentials', [ExnessController::class, 'saveCredentials']);
});

// Admin Routes
Route::middleware(['web', 'auth', 'verified', \App\Http\Middleware\HasAccessAdmin::class, \App\Http\Middleware\Admin\HandleInertiaAdminRequests::class])->prefix('admin')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Admin/Dashboard');
    })->name('admin.dashboard');

    // Reports Routes
    Route::get('/reports/clients', function () {
        return Inertia::render('Admin/Report/Clients');
    })->name('admin.reports.clients');

    Route::get('/reports/client-account', function () {
        return Inertia::render('Admin/Report/ClientAccount');
    })->name('admin.reports.client-account');

    Route::get('/reports/reward-history', function () {
        return Inertia::render('Admin/Report/RewardHistory');
    })->name('admin.reports.reward-history');

    Route::get('/reports/client-transaction', function () {
        return Inertia::render('Admin/Report/ClientTransaction');
    })->name('admin.reports.client-transaction');

    Route::get('/reports/transactions-pending', function () {
        return Inertia::render('Admin/Report/TransactionsPending');
    })->name('admin.reports.transactions-pending');
});

// Route::get('/exness/test', [ExnessController::class, 'test']);
Route::get('/exness/test', [ExnessController::class, 'getClients']);
Route::get('/api/exness/clients', [ExnessController::class, 'getClients']);
Route::get('/api/exness/clients/v1', [ExnessController::class, 'clientsV1']);
Route::get('/api/exness/clients/v2', [ExnessController::class, 'clientsV2']);
Route::get('/api/wallet/accounts', [ExnessController::class, 'getWalletAccounts'])->middleware(['auth', 'verified']);
Route::get('/exness/credentials', [ExnessController::class, 'credentials'])->name('exness.credentials');
Route::post('/exness/credentials', [ExnessController::class, 'updateCredentials'])->name('exness.credentials.update');

require __DIR__.'/auth.php';
