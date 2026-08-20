<?php

declare(strict_types=1);

namespace AhmedNour\AttributeRouting;

use AhmedNour\AttributeRouting\Console\ListAttributeRoutesCommand;
use AhmedNour\AttributeRouting\Discovery\ClassFinder;
use AhmedNour\AttributeRouting\Discovery\RouteDiscovery;
use AhmedNour\AttributeRouting\Discovery\RouteRegistrar;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\ServiceProvider;

final class AttributeRoutingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/attribute-routing.php', 'attribute-routing');

        $this->app->bind(ClassFinder::class, function ($app): ClassFinder {
            /** @var Repository $config */
            $config = $app['config'];

            /** @var array<string, string> $paths */
            $paths = $config->get('attribute-routing.paths', []);

            return new ClassFinder($paths);
        });

        $this->app->bind(RouteDiscovery::class, function ($app): RouteDiscovery {
            /** @var Repository $config */
            $config = $app['config'];

            return new RouteDiscovery(
                (string) $config->get('attribute-routing.permission_middleware', 'permission:%s'),
            );
        });

        $this->app->bind(RouteRegistrar::class, fn ($app): RouteRegistrar => new RouteRegistrar($app['router']));
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/attribute-routing.php' => $this->app->configPath('attribute-routing.php'),
            ], 'attribute-routing-config');

            $this->commands([ListAttributeRoutesCommand::class]);
        }

        if (! $this->shouldDiscover()) {
            return;
        }

        $this->app->make(RouteRegistrar::class)->registerAll(
            $this->app->make(RouteDiscovery::class)->discoverAll(
                $this->app->make(ClassFinder::class)->classes(),
            ),
        );
    }

    private function shouldDiscover(): bool
    {
        /** @var Repository $config */
        $config = $this->app['config'];

        // Cached routes already contain everything discovery would produce,
        // so production boots pay nothing for this package.
        return (bool) $config->get('attribute-routing.enabled', true)
            && ! $this->app->routesAreCached();
    }
}
