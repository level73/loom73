<?php

declare(strict_types=1);

namespace Loom73\Tests\Fixtures\Routing;

use Loom73\Woodframe\Ctrl;

/**
 * Minimal controller hierarchy for routing checks
 * Methods are inspected, never executed
 */

class CoreController extends Ctrl
{
    public function index(): void {}
}

final class ApplicationController extends CoreController
{
    public function custom(): void {}
    protected function protectedAction(): void {}
    private function privateAction(): void {}
    public static function staticAction(): void {}
    public function __invoke(): void {}

    /** Arguments */
    public function details(string $id, string $format = 'html'): void {}
    public function search(string $query, string ...$tags): void {}
}