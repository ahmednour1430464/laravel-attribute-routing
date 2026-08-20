<?php

declare(strict_types=1);

namespace AhmedNour\AttributeRouting\Attributes;

use Attribute;

/**
 * Prefix every route below it with a URI segment.
 *
 * On a class it applies to all routes in that controller; on a method it is
 * appended after the class-level prefix.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Prefix
{
    public function __construct(
        public string $prefix,
    ) {}
}
