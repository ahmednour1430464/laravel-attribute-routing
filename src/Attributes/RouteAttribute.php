<?php

declare(strict_types=1);

namespace AhmedNour\AttributeRouting\Attributes;

/**
 * Base class for every route attribute.
 *
 * Route attributes are pure data: they describe a route but never touch the
 * router themselves. Resolving inherited class-level metadata is the job of
 * the RouteDiscovery, and registration is the job of the RouteRegistrar.
 *
 * Extend this class to add your own verb attribute.
 */
abstract class RouteAttribute
{
    /**
     * @param  string  $path  URI path, relative to any inherited prefix.
     * @param  string|array<int, string>  $middleware  Middleware applied to this route only.
     * @param  string  $prefix  Extra prefix, appended after any class-level prefix.
     * @param  string  $name  Route name, appended after any class-level name prefix.
     * @param  array<string, string>  $where  Regular expression constraints, keyed by parameter.
     * @param  string|array<int, string>  $withoutMiddleware  Inherited middleware to opt out of.
     */
    public function __construct(
        public string $path = '',
        public string|array $middleware = [],
        public string $prefix = '',
        public string $name = '',
        public array $where = [],
        public string|array $withoutMiddleware = [],
    ) {}

    /**
     * The HTTP verbs this attribute registers.
     *
     * @return array<int, string>
     */
    abstract public function methods(): array;
}
