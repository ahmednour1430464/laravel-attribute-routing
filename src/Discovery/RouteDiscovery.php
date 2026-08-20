<?php

declare(strict_types=1);

namespace AhmedNour\AttributeRouting\Discovery;

use AhmedNour\AttributeRouting\Attributes\Middleware;
use AhmedNour\AttributeRouting\Attributes\Name;
use AhmedNour\AttributeRouting\Attributes\Prefix;
use AhmedNour\AttributeRouting\Attributes\RouteAttribute;
use AhmedNour\AttributeRouting\Attributes\SkipDiscovery;
use AhmedNour\AttributeRouting\Attributes\Throttle;
use AhmedNour\AttributeRouting\Attributes\Version;
use AhmedNour\AttributeRouting\Attributes\WithoutMiddleware;
use AhmedNour\AttributeRouting\Attributes\WithPermission;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

/**
 * Reads route attributes off a class and resolves them into DiscoveredRoutes.
 *
 * Class-level metadata (prefix, middleware, permissions, throttle, name) is
 * inherited by every route in the class; method-level metadata stacks on top.
 */
final readonly class RouteDiscovery
{
    /**
     * @param  string  $permissionFormat  sprintf format used for plain enum / string permissions.
     */
    public function __construct(
        private string $permissionFormat = 'permission:%s',
    ) {}

    /**
     * @param  iterable<class-string>  $classes
     * @return list<DiscoveredRoute>
     */
    public function discoverAll(iterable $classes): array
    {
        $routes = [];

        foreach ($classes as $class) {
            foreach ($this->discover($class) as $route) {
                $routes[] = $route;
            }
        }

        return $routes;
    }

    /**
     * @param  class-string  $class
     * @return list<DiscoveredRoute>
     */
    public function discover(string $class): array
    {
        $reflection = new ReflectionClass($class);

        if ($this->shouldSkip($reflection)) {
            return [];
        }

        $classContext = $this->contextFrom($reflection);
        $routes = [];

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            // Inherited methods belong to the parent that declared them, so a
            // base controller never registers its routes once per subclass.
            // Trait methods report the using class here, so they still count.
            if ($method->getDeclaringClass()->getName() !== $reflection->getName()) {
                continue;
            }

            $attributes = $method->getAttributes(RouteAttribute::class, ReflectionAttribute::IS_INSTANCEOF);

            if ($attributes === []) {
                continue;
            }

            $methodContext = $this->contextFrom($method);

            foreach ($attributes as $attribute) {
                /** @var RouteAttribute $route */
                $route = $attribute->newInstance();

                $routes[] = $this->resolve($route, $classContext, $methodContext, [
                    $reflection->getName(),
                    $method->getName(),
                ]);
            }
        }

        return $routes;
    }

    /**
     * @param  array{middleware: list<string>, prefixes: list<string>, name: string, without: list<string>}  $class
     * @param  array{middleware: list<string>, prefixes: list<string>, name: string, without: list<string>}  $method
     * @param  array{0: class-string, 1: string}  $action
     */
    private function resolve(RouteAttribute $route, array $class, array $method, array $action): DiscoveredRoute
    {
        $middleware = [
            ...$class['middleware'],
            ...$method['middleware'],
            ...$this->toArray($route->middleware),
        ];

        $withoutMiddleware = [
            ...$class['without'],
            ...$method['without'],
            ...$this->toArray($route->withoutMiddleware),
        ];

        $uri = $this->normalizeUri(implode('/', [
            ...$class['prefixes'],
            ...$method['prefixes'],
            $route->prefix,
            $route->path,
        ]));

        // A method-level #[Name] wins over the attribute's own name: argument.
        // A route is only named when the method itself supplies a name; a
        // class-level #[Name] is a prefix, never a name in its own right.
        $methodName = $method['name'] !== '' ? $method['name'] : $route->name;

        $name = $methodName !== ''
            ? $class['name'].$methodName
            : null;

        return new DiscoveredRoute(
            methods: $route->methods(),
            uri: $uri,
            action: $action,
            middleware: $this->unique($middleware),
            withoutMiddleware: $this->unique($withoutMiddleware),
            name: $name,
            where: $route->where,
        );
    }

    /**
     * Collect the modifier attributes declared on a class or a method.
     *
     * @return array{middleware: list<string>, prefixes: list<string>, name: string, without: list<string>}
     */
    private function contextFrom(ReflectionClass|ReflectionMethod $reflector): array
    {
        $middleware = [];
        $prefixes = [];
        $without = [];
        $name = '';

        foreach ($reflector->getAttributes() as $attribute) {
            switch ($attribute->getName()) {
                case Middleware::class:
                    /** @var Middleware $instance */
                    $instance = $attribute->newInstance();
                    $middleware = [...$middleware, ...$this->toArray($instance->middleware)];
                    break;
                case WithPermission::class:
                    /** @var WithPermission $instance */
                    $instance = $attribute->newInstance();
                    $middleware = [...$middleware, ...$instance->toMiddleware($this->permissionFormat)];
                    break;
                case Throttle::class:
                    /** @var Throttle $instance */
                    $instance = $attribute->newInstance();
                    $middleware[] = $instance->middleware;
                    break;
                case Prefix::class:
                    /** @var Prefix $instance */
                    $instance = $attribute->newInstance();
                    $prefixes[] = $instance->prefix;
                    break;
                case Version::class:
                    /** @var Version $instance */
                    $instance = $attribute->newInstance();
                    $prefixes[] = $instance->version;
                    break;
                case Name::class:
                    /** @var Name $instance */
                    $instance = $attribute->newInstance();
                    $name = $instance->name;
                    break;
                case WithoutMiddleware::class:
                    /** @var WithoutMiddleware $instance */
                    $instance = $attribute->newInstance();
                    $without = [...$without, ...$instance->middleware];
                    break;
            }
        }

        return [
            'middleware' => $middleware,
            'prefixes' => $prefixes,
            'name' => $name,
            'without' => $without,
        ];
    }

    private function shouldSkip(ReflectionClass $reflection): bool
    {
        return $reflection->isAbstract()
            || $reflection->isInterface()
            || $reflection->isEnum()
            || $reflection->getAttributes(SkipDiscovery::class) !== [];
    }

    private function normalizeUri(string $uri): string
    {
        $uri = preg_replace('#/+#', '/', $uri) ?? $uri;

        return trim($uri, '/');
    }

    /**
     * @param  string|array<int, string>  $value
     * @return list<string>
     */
    private function toArray(string|array $value): array
    {
        return array_values(is_string($value) ? [$value] : $value);
    }

    /**
     * @param  list<string>  $middleware
     * @return list<string>
     */
    private function unique(array $middleware): array
    {
        return array_values(array_unique(array_filter($middleware, static fn (string $item): bool => $item !== '')));
    }
}
