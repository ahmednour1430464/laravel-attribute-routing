<?php

declare(strict_types=1);

namespace AhmedNour\AttributeRouting\Discovery;

/**
 * A route resolved from attributes, before it reaches the router.
 *
 * Keeping this as plain data means discovery can be tested without booting a
 * router, and the router can be swapped without touching discovery.
 */
final readonly class DiscoveredRoute
{
    /**
     * @param  array<int, string>  $methods  HTTP verbs.
     * @param  string  $uri  Fully resolved URI, prefixes already applied.
     * @param  array{0: class-string, 1: string}  $action  Controller class and method.
     * @param  array<int, string>  $middleware
     * @param  array<int, string>  $withoutMiddleware
     * @param  array<string, string>  $where
     */
    public function __construct(
        public array $methods,
        public string $uri,
        public array $action,
        public array $middleware = [],
        public array $withoutMiddleware = [],
        public ?string $name = null,
        public array $where = [],
    ) {}

    /**
     * Human readable source of the route, e.g. "App\Http\Controllers\LeadController@index".
     */
    public function source(): string
    {
        return $this->action[0].'@'.$this->action[1];
    }
}
