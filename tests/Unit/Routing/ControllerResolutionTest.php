<?php

declare(strict_types=1);

namespace Loom73\Tests\Unit\Routing;

use Loom73\Tests\Fixtures\Routing\ApplicationController;
use Loom73\Tests\Fixtures\Routing\CoreController;
use PHPUnit\Framework\TestCase;

require_once ROOT_DIR . '/tests/Fixtures/Routing/Controllers.php';

/**
 * Verifies controller selection against a small, read-only fixture instance.
 *
 * The resolver runs in a separate PHP process so ROOT_DIR can point to the
 * fixture without changing the main test bootstrap or application files.
 */
final class ControllerResolutionTest extends TestCase
{
    public function testExplicitOverrideTakesPrecedenceOverApplicationAndCore(): void
    {
        self::assertSame(
            ApplicationController::class,
            $this->resolve([
                'core' => ['example' => CoreController::class],
                'overrides' => ['example' => ApplicationController::class],
            ], 'example', 'Example')
        );
    }

    public function testApplicationControllerTakesPrecedenceOverCore(): void
    {
        self::assertSame(
            'Loom73\\Weave\\Controllers\\ExampleCtrl',
            $this->resolve([
                'core' => ['example' => CoreController::class],
            ], 'example', 'Example')
        );
    }

    public function testCoreControllerIsUsedWhenApplicationFileIsMissing(): void
    {
        self::assertSame(
            CoreController::class,
            $this->resolve([
                'core' => ['missing' => CoreController::class],
            ], 'missing', 'Missing')
        );
    }

    public function testUnmappedRouteReturnsConventionalApplicationClassName(): void
    {
        self::assertSame(
            'Loom73\\Weave\\Controllers\\MissingCtrl',
            $this->resolve([], 'missing', 'Missing')
        );
    }

    /**
     * Runs the real resolver with fixture routes and captures its selected class.
     */
    private function resolve(
        array $routes,
        string $route,
        string $baseClassName
    ): string {
        $process = proc_open(
            [
                PHP_BINARY,
                ROOT_DIR . '/tests/Fixtures/Routing/resolve-controller.php',
                json_encode($routes, JSON_THROW_ON_ERROR),
                $route,
                $baseClassName,
            ],
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            ROOT_DIR
        );

        if (!is_resource($process)):
            self::fail('Unable to start the resolver fixture.');
        endif;

        fclose($pipes[0]);

        $output = stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        self::assertSame(0, proc_close($process), $error);

        return $output;
    }
}