<?php

declare(strict_types=1);

namespace AhmedNour\AttributeRouting\Tests\Fixtures\Controllers;

use AhmedNour\AttributeRouting\Attributes\Get;
use AhmedNour\AttributeRouting\Attributes\SkipDiscovery;

#[SkipDiscovery]
class SkippedController
{
    #[Get('never-registered', name: 'skipped')]
    public function index(): string
    {
        return 'index';
    }
}
