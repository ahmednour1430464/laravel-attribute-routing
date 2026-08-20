<?php

declare(strict_types=1);

namespace AhmedNour\AttributeRouting\Attributes;

use Attribute;

/**
 * Register the method for every HTTP verb.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Any extends RouteAttribute
{
    /**
     * {@inheritDoc}
     */
    public function methods(): array
    {
        return ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];
    }
}
