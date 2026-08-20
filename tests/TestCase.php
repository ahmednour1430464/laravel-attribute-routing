<?php

declare(strict_types=1);

namespace AhmedNour\AttributeRouting\Tests;

use AhmedNour\AttributeRouting\AttributeRoutingServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [AttributeRoutingServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('attribute-routing.paths', [
            'AhmedNour\\AttributeRouting\\Tests\\Fixtures\\' => __DIR__.'/Fixtures',
        ]);
    }
}
