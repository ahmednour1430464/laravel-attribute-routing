<?php

declare(strict_types=1);

namespace AhmedNour\AttributeRouting\Console;

use AhmedNour\AttributeRouting\Discovery\ClassFinder;
use AhmedNour\AttributeRouting\Discovery\DiscoveredRoute;
use AhmedNour\AttributeRouting\Discovery\RouteDiscovery;
use Closure;
use Illuminate\Console\Command;
use Symfony\Component\Console\Terminal;

/**
 * Answers the only question people ask when a route does not show up:
 * "was my controller even scanned?"
 *
 * Rendering deliberately mirrors `php artisan route:list` so the two read as
 * the same tool: dot leaders, coloured verbs, middleware behind -v.
 */
final class ListAttributeRoutesCommand extends Command
{
    /**
     * The verbs Laravel's Router registers for a route that accepts anything.
     */
    private const ANY_VERBS = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

    private const DIM = '#6C7280';

    protected $signature = 'attribute-routing:list
        {--path= : Only show routes whose URI contains this string}';

    protected $description = 'List the routes discovered from PHP attributes, and where each came from';

    /**
     * @var array<string, string>
     */
    private array $verbColors = [
        'ANY' => 'red',
        'GET' => 'blue',
        'HEAD' => self::DIM,
        'OPTIONS' => self::DIM,
        'POST' => 'yellow',
        'PUT' => 'yellow',
        'PATCH' => 'yellow',
        'DELETE' => 'red',
    ];

    private static ?Closure $terminalWidthResolver = null;

    public function handle(ClassFinder $finder, RouteDiscovery $discovery): int
    {
        $classes = $finder->classes();
        $routes = $discovery->discoverAll($classes);

        if ($filter = $this->option('path')) {
            $routes = array_values(array_filter(
                $routes,
                static fn (DiscoveredRoute $route): bool => str_contains($route->uri, (string) $filter),
            ));
        }

        if ($routes === []) {
            $this->components->warn(sprintf(
                'No attribute routes found. %d class(es) were scanned — check the "paths" key in config/attribute-routing.php.',
                count($classes),
            ));

            return self::SUCCESS;
        }

        $this->output->writeln($this->render($routes, count($classes)));

        return self::SUCCESS;
    }

    /**
     * Override how the terminal width is resolved. Mainly for tests, so output
     * does not reflow with the window it happens to run in.
     */
    public static function resolveTerminalWidthUsing(?Closure $resolver): void
    {
        self::$terminalWidthResolver = $resolver;
    }

    /**
     * @param  list<DiscoveredRoute>  $routes
     * @return list<string>
     */
    private function render(array $routes, int $scanned): array
    {
        $width = self::terminalWidth();

        $maxMethod = max(array_map(
            fn (DiscoveredRoute $route): int => mb_strlen($this->methodLabel($route)),
            $routes,
        ));

        $lines = [''];

        foreach ($routes as $route) {
            $lines[] = $this->renderRoute($route, $maxMethod, $width);

            foreach ($this->renderMiddleware($route, $maxMethod) as $line) {
                $lines[] = $line;
            }
        }

        $lines[] = '';
        $lines[] = $this->renderSummary(count($routes), $scanned, $width);
        $lines[] = '';

        return $lines;
    }

    private function renderRoute(DiscoveredRoute $route, int $maxMethod, int $width): string
    {
        $method = $this->methodLabel($route);
        $uri = '/'.$route->uri;
        $action = $this->actionLabel($route);

        $spaces = str_repeat(' ', max($maxMethod + 6 - mb_strlen($method), 0));

        $dots = str_repeat('.', max(
            $width - mb_strlen($method.$spaces.$uri.$action) - 6 - ($action !== '' ? 1 : 0),
            0,
        ));

        $dots = $dots === '' ? '' : ' '.$dots;

        // Long controller names get an ellipsis rather than wrapping the line,
        // unless the reader asked for everything with -v.
        if ($action !== '' && ! $this->output->isVerbose()
            && mb_strlen($method.$spaces.$uri.$action.$dots) > ($width - 6)) {
            $keep = $width - 7 - mb_strlen($method.$spaces.$uri.$dots);
            $action = $keep > 1 ? mb_substr($action, 0, $keep).'…' : '';
        }

        return sprintf(
            '  <fg=white;options=bold>%s</>%s<fg=white>%s</><fg=%s>%s %s</>',
            $this->colorizeMethod($method),
            $spaces,
            $this->highlightParameters($uri),
            self::DIM,
            $dots,
            $action,
        );
    }

    /**
     * @return list<string>
     */
    private function renderMiddleware(DiscoveredRoute $route, int $maxMethod): array
    {
        if (! $this->output->isVerbose()) {
            return [];
        }

        $indent = str_repeat(' ', $maxMethod + 9);

        $lines = array_map(
            static fn (string $middleware): string => $indent.'⇂ '.$middleware,
            $route->middleware,
        );

        foreach ($route->withoutMiddleware as $excluded) {
            $lines[] = $indent.'⇂ ✗ '.$excluded;
        }

        foreach ($route->where as $parameter => $expression) {
            $lines[] = $indent.'⇂ where '.$parameter.' = '.$expression;
        }

        return array_map(
            static fn (string $line): string => sprintf('<fg=%s>%s</>', self::DIM, $line),
            $lines,
        );
    }

    private function renderSummary(int $routes, int $scanned, int $width): string
    {
        $text = sprintf(
            'Showing [%d] %s from [%d] scanned %s',
            $routes,
            $routes === 1 ? 'route' : 'routes',
            $scanned,
            $scanned === 1 ? 'class' : 'classes',
        );

        $offset = max($width - mb_strlen($text) - 2, 0);

        return str_repeat(' ', $offset).'<fg=blue;options=bold>'.$text.'</>';
    }

    /**
     * "ANY" reads better than seven verbs pushing the URI off the screen.
     */
    private function methodLabel(DiscoveredRoute $route): string
    {
        return $route->methods === self::ANY_VERBS
            ? 'ANY'
            : implode('|', $route->methods);
    }

    /**
     * "leads.index › LeadController@index" — the route name, then where it lives.
     */
    private function actionLabel(DiscoveredRoute $route): string
    {
        $action = $this->output->isVerbose()
            ? $route->source()
            : $this->shortenClass($route->source());

        return $route->name === null ? $action : $route->name.' › '.$action;
    }

    /**
     * Drop the application namespace so the eye lands on the class name. The
     * full path is one -v away.
     */
    private function shortenClass(string $source): string
    {
        $namespace = $this->laravel->getNamespace();

        foreach ([$namespace.'Http\\Controllers\\', $namespace] as $prefix) {
            if (str_starts_with($source, $prefix)) {
                return mb_substr($source, mb_strlen($prefix));
            }
        }

        return $source;
    }

    private function colorizeMethod(string $method): string
    {
        $verbs = array_map(
            fn (string $verb): string => sprintf('<fg=%s>%s</>', $this->verbColors[$verb] ?? 'default', $verb),
            explode('|', $method),
        );

        return implode(sprintf('<fg=%s>|</>', self::DIM), $verbs);
    }

    private function highlightParameters(string $uri): string
    {
        return preg_replace('#(\{[^}]+\})#', '<fg=yellow>$1</>', $uri) ?? $uri;
    }

    private static function terminalWidth(): int
    {
        return self::$terminalWidthResolver === null
            ? (new Terminal)->getWidth()
            : (int) call_user_func(self::$terminalWidthResolver);
    }
}
