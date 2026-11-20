<?php

namespace TheAfolayan\HmsLaravel\Tests;

use Illuminate\Support\ServiceProvider;
use TheAfolayan\HmsLaravel\Facades\Hms;
use TheAfolayan\HmsLaravel\HmsServiceProvider;
use TheAfolayan\HmsLaravel\Services\HmsService;

class HmsServiceProviderTest extends TestCase
{
    public function testItBindsTheServiceAsASingleton()
    {
        $service = $this->app->make('100ms');

        $this->assertInstanceOf(HmsService::class, $service);
        $this->assertSame($service, $this->app->make('100ms'));
    }

    public function testTheFacadeResolvesTheService()
    {
        $this->assertInstanceOf(HmsService::class, Hms::getFacadeRoot());
    }

    public function testItMergesTheDefaultConfig()
    {
        $this->assertSame('https://api.100ms.live/v2/', config('100ms.base_url'));
        $this->assertSame(self::API_KEY, config('100ms.api_key'));
    }

    public function testTheConfigFileIsPublishable()
    {
        $paths = ServiceProvider::pathsToPublish(HmsServiceProvider::class, '100ms-config');

        $this->assertContains(config_path('100ms.php'), $paths);
    }
}
