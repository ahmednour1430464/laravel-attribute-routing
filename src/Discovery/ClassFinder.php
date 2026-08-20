<?php

declare(strict_types=1);

namespace AhmedNour\AttributeRouting\Discovery;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Turns configured PSR-4 roots into a list of loadable class names.
 *
 * Files without a single `#[` in them are skipped before autoloading, so a
 * large app/ directory is not fully loaded on every boot just to find routes.
 */
final readonly class ClassFinder
{
    /**
     * @param  array<string, string>  $paths  Map of PSR-4 namespace prefix => directory.
     */
    public function __construct(
        private array $paths,
    ) {}

    /**
     * @return list<class-string>
     */
    public function classes(): array
    {
        $classes = [];

        foreach ($this->paths as $namespace => $path) {
            if (! is_dir($path)) {
                continue;
            }

            foreach ($this->phpFiles($path) as $file) {
                $class = $this->toClassName($namespace, $path, $file->getPathname());

                if ($class === null || ! class_exists($class)) {
                    continue;
                }

                $classes[] = $class;
            }
        }

        return array_values(array_unique($classes));
    }

    /**
     * @return iterable<SplFileInfo>
     */
    private function phpFiles(string $path): iterable
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($files as $file) {
            if (! $file instanceof SplFileInfo || $file->getExtension() !== 'php') {
                continue;
            }

            // Cheap pre-filter: no attribute syntax means nothing to discover.
            $contents = @file_get_contents($file->getPathname());

            if ($contents === false || ! str_contains($contents, '#[')) {
                continue;
            }

            yield $file;
        }
    }

    /**
     * @return class-string|null
     */
    private function toClassName(string $namespace, string $root, string $file): ?string
    {
        $root = rtrim(str_replace('\\', '/', realpath($root) ?: $root), '/');
        $file = str_replace('\\', '/', realpath($file) ?: $file);

        if (! str_starts_with($file, $root.'/')) {
            return null;
        }

        $relative = substr($file, strlen($root) + 1);
        $relative = substr($relative, 0, -strlen('.php'));

        /** @var class-string $class */
        $class = rtrim($namespace, '\\').'\\'.str_replace('/', '\\', $relative);

        return $class;
    }
}
