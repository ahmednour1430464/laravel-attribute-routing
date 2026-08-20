<?php

declare(strict_types=1);

namespace AhmedNour\AttributeRouting\Attributes;

use Attribute;

/**
 * Prefix every route below it with an API version segment.
 *
 * Functionally a Prefix, kept separate so `#[Version('v2')]` reads as intent
 * and can be located with a single "find usages".
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Version
{
    public function __construct(
        public string $version,
    ) {}
}
