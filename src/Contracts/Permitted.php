<?php

declare(strict_types=1);

namespace AhmedNour\AttributeRouting\Contracts;

/**
 * Implement this on your permission enum to control the middleware string
 * `#[WithPermission]` produces for each case.
 *
 * ```php
 * enum PermissionEnum: string implements Permitted
 * {
 *     case EDIT_TASK = 'edit_task';
 *
 *     public function getMiddleware(): string
 *     {
 *         return 'permission:'.$this->value;
 *     }
 * }
 * ```
 *
 * A plain BackedEnum works too — it falls back to the `permission_middleware`
 * format configured in config/attribute-routing.php.
 */
interface Permitted
{
    public function getMiddleware(): string;
}
