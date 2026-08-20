<?php

declare(strict_types=1);

namespace AhmedNour\AttributeRouting\Attributes;

use Attribute;

/**
 * Opt out of inherited middleware — the escape hatch for the one login route
 * inside an otherwise authenticated controller.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class WithoutMiddleware
{
    /** @var array<int, string> */
    public readonly array $middleware;

    /**
     * @param  string|array<int, string>  $middleware
     */
    public function __construct(string|array $middleware)
    {
        $this->middleware = is_string($middleware) ? [$middleware] : $middleware;
    }
}
