<?php

declare(strict_types=1);

namespace AhmedNour\AttributeRouting\Attributes;

use Attribute;

/**
 * Attach middleware to every route below it.
 *
 * Class-level middleware runs first, then method-level middleware stacks on top.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Middleware
{
    /**
     * @param  string|array<int, string>  $middleware
     */
    public function __construct(
        public string|array $middleware,
    ) {}
}
