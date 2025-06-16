<?php

namespace App\Providers;

use App\Services\ExnessAuthService;
use App\Services\ExnessClientService;
use App\Services\ExnessWalletService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(ExnessAuthService::class);
        $this->app->singleton(ExnessClientService::class);
        $this->app->singleton(ExnessWalletService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
