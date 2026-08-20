<?php

declare(strict_types=1);

namespace AhmedNour\AttributeRouting\Attributes;

use AhmedNour\AttributeRouting\Contracts\Permitted;
use Attribute;
use BackedEnum;

/**
 * Guard the routes below it with one or more permissions.
 *
 * `#[WithPermission(PermissionEnum::EDIT_TASK)]` on a controller method puts the
 * permission requirement next to the code it protects — and makes "find usages"
 * on an enum case list every route that requires it.
 *
 * Accepts three shapes:
 *  - a {@see Permitted} enum case  → uses its own getMiddleware()
 *  - any other BackedEnum case     → formatted with the configured format string
 *  - a raw string                  → used as-is if it already looks like middleware
 *                                    (contains a `:`), otherwise formatted
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class WithPermission
{
    /** @var array<int, Permitted|BackedEnum|string> */
    public readonly array $permissions;

    public function __construct(Permitted|BackedEnum|string ...$permissions)
    {
        $this->permissions = $permissions;
    }

    /**
     * Resolve the permissions to middleware strings.
     *
     * @param  string  $format  sprintf format applied to non-Permitted values, e.g. 'permission:%s'
     * @return array<int, string>
     */
    public function toMiddleware(string $format): array
    {
        return array_map(
            static fn (Permitted|BackedEnum|string $permission): string => match (true) {
                $permission instanceof Permitted => $permission->getMiddleware(),
                $permission instanceof BackedEnum => sprintf($format, (string) $permission->value),
                str_contains($permission, ':') => $permission,
                default => sprintf($format, $permission),
            },
            $this->permissions,
        );
    }
}
