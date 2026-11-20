<?php

namespace TheAfolayan\HmsLaravel\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use TheAfolayan\HmsLaravel\HmsServiceProvider;

abstract class TestCase extends Orchestra
{
    public const API_KEY = 'test-access-key';

    // firebase/php-jwt 7 rejects HS256 secrets shorter than 32 bytes.
    public const API_SECRET = 'test-secret-that-is-longer-than-thirty-two-bytes';

    protected function getPackageProviders($app)
    {
        return [HmsServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('100ms.api_key', self::API_KEY);
        $app['config']->set('100ms.api_secret', self::API_SECRET);
    }
}
