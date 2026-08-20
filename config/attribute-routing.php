<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | Turn attribute route discovery off without uninstalling the package.
    | Discovery is skipped automatically whenever routes are cached, so this
    | only matters for local and testing environments.
    |
    */

    'enabled' => env('ATTRIBUTE_ROUTING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Discovery Paths
    |--------------------------------------------------------------------------
    |
    | PSR-4 namespace prefix => directory. Every PHP file below each directory
    | that contains attribute syntax is loaded and scanned for route attributes.
    | Keep this list as tight as your layout allows — pointing it at a single
    | Http/Controllers directory is faster than scanning all of app/.
    |
    */

    'paths' => [
        'App\\' => app_path(),
    ],

    /*
    |--------------------------------------------------------------------------
    | Permission Middleware Format
    |--------------------------------------------------------------------------
    |
    | Used by #[WithPermission] for permissions that are plain backed enums or
    | strings. Enums implementing the Permitted contract build their own
    | middleware string and ignore this value.
    |
    */

    'permission_middleware' => 'permission:%s',

];
