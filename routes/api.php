<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExnessController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Exness API Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/exness/token', [ExnessController::class, 'getToken']);
    Route::get('/exness/clients', [ExnessController::class, 'getClients']);
    Route::get('/exness/wallets', [ExnessController::class, 'getWallets']);
});