<?php

namespace App\Providers;

use App\Services\OtpService;
use Illuminate\Support\ServiceProvider;

class OtpServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(OtpService::class, function ($app) {
            return new OtpService();
        });
    }

    public function boot()
    {
        //
    }
}
