<?php

declare(strict_types=1);

namespace AhmedNour\AttributeRouting\Tests\Fixtures\Controllers;

use AhmedNour\AttributeRouting\Attributes\Get;
use AhmedNour\AttributeRouting\Attributes\Prefix;

#[Prefix('child')]
class ChildController extends BaseController
{
    #[Get('own', name: 'child.own')]
    public function own(): string
    {
        return 'own';
    }
}
