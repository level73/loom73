<?php

declare(strict_types=1);

namespace Loom73\Tests\Unit;

use Loom73\Beam\QueryResult;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Verifies class loading through Loom73's registered autoloader.
 *
 * Each test starts in a fresh PHP process so earlier tests cannot
 * satisfy class lookups using classes that are already loaded.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class AutoloadTest extends TestCase
{
    /**
     * A core class must be loaded from the file matching its namespace.
     */
    public function testCoreClassLoadsFromItsNamespacePath(): void
    {
        self::assertFalse(class_exists(QueryResult::class, false));

        self::assertTrue(class_exists(QueryResult::class));

        self::assertSame(
            realpath(ROOT_DIR . '/lib/Beam/QueryResult.php'),
            (new ReflectionClass(QueryResult::class))->getFileName()
        );
    }

    /**
     * Missing classes must allow normal PHP class lookup to fail quietly.
     */
    public function testMissingClassProducesNoOutput(): void
    {
        $this->expectOutputString('');

        self::assertFalse(
            class_exists('Loom73\\Tests\\MissingAutoloadClass')
        );
    }
}