<?php

declare(strict_types=1);

namespace AhmedNour\AttributeRouting\Discovery;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;

/**
 * The only place in the package that talks to Laravel's router.
 */
final readonly class RouteRegistrar
{
    public function __construct(
        private Router $router,
    ) {}

    /**
     * @param  iterable<DiscoveredRoute>  $routes
     */
    public function registerAll(iterable $routes): void
    {
        foreach ($routes as $route) {
            $this->register($route);
        }
    }

    public function register(DiscoveredRoute $discovered): Route
    {
        $route = $this->router->match(
            $discovered->methods,
            $discovered->uri === '' ? '/' : $discovered->uri,
            $discovered->action,
        );

        if ($discovered->middleware !== []) {
            $route->middleware($discovered->middleware);
        }

        if ($discovered->withoutMiddleware !== []) {
            $route->withoutMiddleware($discovered->withoutMiddleware);
        }

        if ($discovered->name !== null) {
            $route->name($discovered->name);
        }

        foreach ($discovered->where as $parameter => $expression) {
            $route->where($parameter, $expression);
        }

        return $route;
    }
}
