<?php

declare(strict_types=1);

namespace AhmedNour\AttributeRouting\Console;

use AhmedNour\AttributeRouting\Discovery\ClassFinder;
use AhmedNour\AttributeRouting\Discovery\DiscoveredRoute;
use AhmedNour\AttributeRouting\Discovery\RouteDiscovery;
use Illuminate\Console\Command;

/**
 * Answers the only question people ask when a route does not show up:
 * "was my controller even scanned?"
 */
final class ListAttributeRoutesCommand extends Command
{
    protected $signature = 'attribute-routing:list
        {--path= : Only show routes whose URI contains this string}';

    protected $description = 'List the routes discovered from PHP attributes, and where each came from';

    public function handle(ClassFinder $finder, RouteDiscovery $discovery): int
    {
        $classes = $finder->classes();
        $routes = $discovery->discoverAll($classes);

        if ($filter = $this->option('path')) {
            $routes = array_values(array_filter(
                $routes,
                static fn (DiscoveredRoute $route): bool => str_contains($route->uri, (string) $filter),
            ));
        }

        if ($routes === []) {
            $this->components->warn(sprintf(
                'No attribute routes found. %d class(es) were scanned — check the "paths" key in config/attribute-routing.php.',
                count($classes),
            ));

            return self::SUCCESS;
        }

        $this->table(
            ['Method', 'URI', 'Name', 'Action', 'Middleware'],
            array_map(static fn (DiscoveredRoute $route): array => [
                implode('|', $route->methods),
                '/'.$route->uri,
                $route->name ?? '',
                $route->source(),
                implode(', ', $route->middleware),
            ], $routes),
        );

        $this->components->info(sprintf('%d route(s) from %d scanned class(es).', count($routes), count($classes)));

        return self::SUCCESS;
    }
}
