<?php

declare(strict_types=1);

namespace AhmedNour\AttributeRouting\Tests\Fixtures\Controllers;

use AhmedNour\AttributeRouting\Attributes\Any;
use AhmedNour\AttributeRouting\Attributes\MatchRoute;
use AhmedNour\AttributeRouting\Attributes\Middleware;
use AhmedNour\AttributeRouting\Attributes\Post;
use AhmedNour\AttributeRouting\Attributes\Prefix;
use AhmedNour\AttributeRouting\Attributes\Throttle;
use AhmedNour\AttributeRouting\Attributes\WithoutMiddleware;
use AhmedNour\AttributeRouting\Attributes\WithPermission;
use AhmedNour\AttributeRouting\Tests\Fixtures\Enums\PlainPermission;

#[Prefix('api/auth')]
#[Middleware(['api', 'auth:sanctum'])]
class LoginController
{
    #[Post('login', name: 'auth.login')]
    #[WithoutMiddleware('auth:sanctum')]
    #[Throttle(5, 2)]
    public function login(): string
    {
        return 'login';
    }

    #[MatchRoute(['put', 'patch'], 'password', name: 'auth.password')]
    public function password(): string
    {
        return 'password';
    }

    #[Any('probe', name: 'auth.probe')]
    public function probe(): string
    {
        return 'probe';
    }

    #[Post('export', name: 'auth.export')]
    #[WithPermission(PlainPermission::EXPORT_REPORT, 'can:download-audit')]
    public function export(): string
    {
        return 'export';
    }
}
