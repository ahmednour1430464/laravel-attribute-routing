<?php

declare(strict_types=1);

namespace AhmedNour\AttributeRouting\Tests\Unit;

use AhmedNour\AttributeRouting\Discovery\ClassFinder;
use AhmedNour\AttributeRouting\Tests\Fixtures\Controllers\LeadController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ClassFinderTest extends TestCase
{
    #[Test]
    public function it_maps_files_under_a_psr4_root_to_class_names(): void
    {
        $finder = new ClassFinder([
            'AhmedNour\\AttributeRouting\\Tests\\Fixtures\\' => __DIR__.'/../Fixtures',
        ]);

        $this->assertContains(LeadController::class, $finder->classes());
    }

    #[Test]
    public function it_ignores_directories_that_do_not_exist(): void
    {
        $finder = new ClassFinder(['Nope\\' => __DIR__.'/does-not-exist']);

        $this->assertSame([], $finder->classes());
    }
}
