<?php

namespace TheAfolayan\HmsLaravel;

use Illuminate\Support\ServiceProvider;

class HmsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/Config/100ms.php', '100ms');

        $this->app->singleton('100ms', function () {
            return new Services\HmsService(
                config('100ms.api_key'),
                config('100ms.api_secret'),
                config('100ms.base_url')
            );
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/Config/100ms.php' => config_path('100ms.php'),
        ]);
    }
}
