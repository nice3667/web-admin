<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\ExnessSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    private ExnessSyncService $exnessSyncService;

    public function __construct(ExnessSyncService $exnessSyncService)
    {
        $this->exnessSyncService = $exnessSyncService;
    }
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        Log::info('Login attempt started', ['email' => $request->email]);
        
        $request->authenticate();
        $request->session()->regenerate();

        Log::info('Laravel authentication successful');

        $user = Auth::user();

        // Sync Exness data for this user
        try {
            Log::info('Starting Exness data sync for user', ['user_id' => $user->id]);
            
            $syncResult = $this->exnessSyncService->syncUserDataOnLogin(
                $user, 
                $request->email, 
                $request->password
            );

            if ($syncResult) {
                session([
                    'exness_sync_status' => 'success',
                    'exness_sync_message' => 'ข้อมูล Exness ถูกซิงค์เรียบร้อยแล้ว'
                ]);
                Log::info('Exness sync successful for user', ['user_id' => $user->id]);
            } else {
                session([
                    'exness_sync_status' => 'failed',
                    'exness_sync_message' => 'ไม่สามารถซิงค์ข้อมูล Exness ได้ กรุณาตรวจสอบบัญชี Exness ของคุณ'
                ]);
                Log::warning('Exness sync failed for user', ['user_id' => $user->id]);
            }

        } catch (\Exception $e) {
            Log::error('Error during Exness sync in login', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            session([
                'exness_sync_status' => 'error',
                'exness_sync_message' => 'เกิดข้อผิดพลาดในการซิงค์ข้อมูล Exness'
            ]);
        }

        // Force config update for API URL
        Config::set('app.url', request()->getSchemeAndHttpHost());

        Log::info('Login completed, redirecting to dashboard', ['user_id' => $user->id]);
        
        return redirect()->route('admin.dashboard')->with([
            'api_domain' => request()->getSchemeAndHttpHost(),
            'message' => 'Successfully logged in'
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Log::info('User logging out', ['user_id' => Auth::id()]);
        
        // Clear Exness cache for this user
        if (Auth::check()) {
            $this->exnessSyncService->clearUserCache(Auth::id());
        }
        
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('Logout completed');

        return redirect('/');
    }
}
