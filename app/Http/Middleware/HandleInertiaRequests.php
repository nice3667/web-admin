<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'error' => fn () => $request->session()->get('error'),
                'success' => fn () => $request->session()->get('success'),
            ],
            'config' => [
                'api_domain' => session('api_domain', $request->getSchemeAndHttpHost()),
                'app_url' => config('app.url', $request->getSchemeAndHttpHost()),
            ],
            'exness' => [
                'has_token' => session()->has('exness_token'),
                'has_credentials' => session()->has('exness_credentials'),
                'status' => session()->has('exness_token') ? 'connected' : 'disconnected',
            ],
        ]);
    }
}
