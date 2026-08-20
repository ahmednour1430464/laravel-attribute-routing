<?php

declare(strict_types=1);

namespace AhmedNour\AttributeRouting\Attributes;

use Attribute;

/**
 * Register the method as a POST route.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Post extends RouteAttribute
{
    /**
     * {@inheritDoc}
     */
    public function methods(): array
    {
        return ['POST'];
    }
}
