<?php

namespace Jatniel\Pexels\Tests;

use Jatniel\Pexels\PexelsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            PexelsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('pexels.api_key', 'test-api-key');
        config()->set('pexels.cache.enabled', false);
        config()->set('pexels.rate_limit.enabled', false);
    }
}
