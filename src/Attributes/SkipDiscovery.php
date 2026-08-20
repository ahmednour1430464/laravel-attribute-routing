<?php

declare(strict_types=1);

namespace AhmedNour\AttributeRouting\Attributes;

use Attribute;

/**
 * Exclude a class from route discovery entirely, attributes and all.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class SkipDiscovery {}
