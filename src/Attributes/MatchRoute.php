<?php

declare(strict_types=1);

namespace AhmedNour\AttributeRouting\Attributes;

use Attribute;

/**
 * Register the method for an explicit list of HTTP verbs.
 *
 * Named MatchRoute rather than Match because `match` is a reserved keyword in PHP 8.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class MatchRoute extends RouteAttribute
{
    /** @var array<int, string> */
    private array $methods;

    /**
     * @param  string|array<int, string>  $methods  The HTTP verbs to register.
     * @param  string|array<int, string>  $middleware
     * @param  array<string, string>  $where
     * @param  string|array<int, string>  $withoutMiddleware
     */
    public function __construct(
        string|array $methods,
        string $path = '',
        string|array $middleware = [],
        string $prefix = '',
        string $name = '',
        array $where = [],
        string|array $withoutMiddleware = [],
    ) {
        parent::__construct($path, $middleware, $prefix, $name, $where, $withoutMiddleware);

        $this->methods = array_map(
            static fn (string $method): string => strtoupper($method),
            is_string($methods) ? [$methods] : $methods,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function methods(): array
    {
        return $this->methods;
    }
}
