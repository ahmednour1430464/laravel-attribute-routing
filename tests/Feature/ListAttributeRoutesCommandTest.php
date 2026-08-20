<?php

declare(strict_types=1);

namespace AhmedNour\AttributeRouting\Tests\Feature;

use AhmedNour\AttributeRouting\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;

final class ListAttributeRoutesCommandTest extends TestCase
{
    #[Test]
    public function it_lists_discovered_routes_with_their_source(): void
    {
        $output = $this->runCommand('attribute-routing:list');

        $this->assertStringContainsString('/api/v1/leads', $output);
        $this->assertStringContainsString('leads.index', $output);
        $this->assertStringContainsString('LeadController@index', $output);
        $this->assertStringContainsString('permission:view_leads', $output);
        $this->assertStringContainsString('12 route(s) from 5 scanned class(es).', $output);
    }

    #[Test]
    public function it_filters_by_path(): void
    {
        $output = $this->runCommand('attribute-routing:list', ['--path' => 'auth']);

        $this->assertStringContainsString('auth.login', $output);
        $this->assertStringNotContainsString('leads.index', $output);
    }

    #[Test]
    public function it_warns_when_nothing_was_discovered(): void
    {
        config()->set('attribute-routing.paths', []);

        $this->assertStringContainsString('No attribute routes found', $this->runCommand('attribute-routing:list'));
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    private function runCommand(string $command, array $parameters = []): string
    {
        $this->assertSame(0, Artisan::call($command, $parameters));

        return Artisan::output();
    }
}
