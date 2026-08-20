<?php

declare(strict_types=1);

namespace AhmedNour\AttributeRouting\Tests\Fixtures\Controllers;

use AhmedNour\AttributeRouting\Attributes\Get;

abstract class BaseController
{
    #[Get('inherited', name: 'inherited')]
    public function inherited(): string
    {
        return 'inherited';
    }
}
