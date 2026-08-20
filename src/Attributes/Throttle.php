<?php

declare(strict_types=1);

namespace AhmedNour\AttributeRouting\Attributes;

use Attribute;

/**
 * Rate limit the routes below it: `#[Throttle(60, 1)]` is 60 requests per minute.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class Throttle
{
    public readonly string $middleware;

    public function __construct(
        public readonly int $requests,
        public readonly int $minutes = 1,
    ) {
        $this->middleware = "throttle:{$requests},{$minutes}";
    }
}
