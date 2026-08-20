<?php

declare(strict_types=1);

namespace AhmedNour\AttributeRouting\Tests\Feature;

use AhmedNour\AttributeRouting\Tests\Fixtures\Controllers\LeadController;
use AhmedNour\AttributeRouting\Tests\TestCase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use PHPUnit\Framework\Attributes\Test;

final class RouteRegistrationTest extends TestCase
{
    #[Test]
    public function it_registers_discovered_routes_with_the_router(): void
    {
        $route = $this->routeNamed('leads.index');

        $this->assertSame('api/v1/leads', $route->uri());
        $this->assertContains('GET', $route->methods());
        $this->assertSame(LeadController::class.'@index', $route->getActionName());
    }

    #[Test]
    public function it_applies_middleware_to_the_registered_route(): void
    {
        $this->assertSame(
            ['api', 'auth:sanctum', 'permission:view_leads'],
            $this->routeNamed('leads.index')->middleware(),
        );
    }

    #[Test]
    public function it_applies_where_constraints(): void
    {
        $this->assertSame(['lead' => '[0-9]+'], $this->routeNamed('leads.show')->wheres);
    }

    #[Test]
    public function it_applies_excluded_middleware(): void
    {
        $this->assertSame(['auth:sanctum'], $this->routeNamed('auth.login')->excludedMiddleware());
    }

    #[Test]
    public function it_does_not_register_skipped_controllers(): void
    {
        $this->assertNull(RouteFacade::getRoutes()->getByName('skipped'));
        $this->assertNull(RouteFacade::getRoutes()->getByName('inherited'));
    }

    #[Test]
    public function it_registers_every_verb_for_any_routes(): void
    {
        $methods = $this->routeNamed('auth.probe')->methods();

        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'] as $verb) {
            $this->assertContains($verb, $methods);
        }
    }

    #[Test]
    public function discovered_routes_actually_respond(): void
    {
        $this->withoutMiddleware()
            ->get('/api/v1/leads')
            ->assertOk()
            ->assertSee('index');
    }

    private function routeNamed(string $name): Route
    {
        $route = RouteFacade::getRoutes()->getByName($name);

        $this->assertNotNull($route, "Route [{$name}] was not registered.");

        return $route;
    }
}
