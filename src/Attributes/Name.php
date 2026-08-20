<?php

declare(strict_types=1);

namespace AhmedNour\AttributeRouting\Attributes;

use Attribute;

/**
 * Name routes below it.
 *
 * On a class this is a *prefix* — usually written with a trailing dot, e.g.
 * `#[Name('leads.')]` — that is prepended to each method's own name. A route
 * only gets a name when the method itself supplies one, so a class-level
 * prefix can never collapse several routes onto the same name.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class Name
{
    public function __construct(
        public string $name,
    ) {}
}
