<?php

declare(strict_types=1);

namespace AhmedNour\AttributeRouting\Tests\Fixtures\Enums;

/**
 * A backed enum that does NOT implement Permitted, so it falls back to the
 * configured permission_middleware format.
 */
enum PlainPermission: string
{
    case EXPORT_REPORT = 'export_report';
}
