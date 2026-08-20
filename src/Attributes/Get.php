<?php

declare(strict_types=1);

namespace AhmedNour\AttributeRouting\Attributes;

use Attribute;

/**
 * Register the method as a GET route (HEAD is registered alongside it, as Laravel does).
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Get extends RouteAttribute
{
    /**
     * {@inheritDoc}
     */
    public function methods(): array
    {
        return ['GET', 'HEAD'];
    }
}
