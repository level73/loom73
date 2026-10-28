<?php

declare(strict_types=1);

namespace Loom73\Tests\Unit\Routing;

use Loom73\Tests\Fixtures\Routing\ApplicationController;
use Loom73\Tests\Fixtures\Routing\CoreController;
use Loom73\Woodframe\Config;
use PHPUnit\Framework\TestCase;

require_once ROOT_DIR . '/tests/Fixtures/Routing/Controllers.php';

/**
 * Verifies which controller methods may be dispatched as route actions
 * Fixture controllers are inspected without starting the application
 */
final class ActionRoutingTest extends TestCase
{
    protected function setUp(): void
    {
        Config::clear();
    }

    protected function tearDown(): void
    {
        Config::clear();
    }

    public function testActionDeclaredByApplicationControllerIsRoutable(): void
    {
        self::assertTrue(
            \isRoutableAction(ApplicationController::class, 'custom')
        );
    }

    public function testInheritedActionRequiresRegisteredCoreController(): void
    {
        self::assertFalse(
            \isRoutableAction(ApplicationController::class, 'index')
        );
    }

    public function testActionInheritedFromRegisteredCoreControllerIsRoutable(): void
    {
        Config::set('routes.core', [
            'example' => CoreController::class,
        ]);

        self::assertTrue(
            \isRoutableAction(ApplicationController::class, 'index')
        );
    }

    /**
     * Registering a core controller must not expose helpers inherited from Ctrl.
     */
    public function testBaseControllerHelperIsNotRoutable(): void
    {
        Config::set('routes.core', [
            'example' => CoreController::class,
        ]);

        self::assertFalse(
            \isRoutableAction(
                ApplicationController::class,
                'set',
                ['name', 'value']
            )
        );
    }

    public function testProtectedMethodIsNotRoutable(): void
    {
        self::assertFalse(
            \isRoutableAction(ApplicationController::class, 'protectedAction')
        );
    }

    public function testPrivateMethodIsNotRoutable(): void
    {
        self::assertFalse(
            \isRoutableAction(ApplicationController::class, 'privateAction')
        );
    }

    public function testStaticMethodIsNotRoutable(): void
    {
        self::assertFalse(
            \isRoutableAction(ApplicationController::class, 'staticAction')
        );
    }

    /**
     * Magic methods must remain excluded even when public and declared locally.
     */
    public function testMagicMethodIsNotRoutable(): void
    {
        self::assertFalse(
            \isRoutableAction(ApplicationController::class, '__invoke')
        );
    }

    /**
     * Required/Optional arguments
     */
    public function testRequiredAndOptionalArgumentsAreAccepted(): void
    {
        self::assertTrue(
            \isRoutableAction(ApplicationController::class, 'details', ['42'])
        );

        self::assertTrue(
            \isRoutableAction(
                ApplicationController::class,
                'details',
                ['42', 'json']
            )
        );
    }

    public function testMissingAndExtraArgumentsAreRejected(): void
    {
        self::assertFalse(
            \isRoutableAction(ApplicationController::class, 'details')
        );

        self::assertFalse(
            \isRoutableAction(
                ApplicationController::class,
                'details',
                ['42', 'json', 'extra']
            )
        );
    }

    /**
     * Unlimited trailing arguments do not make the first argument optional.
     */
    public function testVariadicActionRespectsItsRequiredArgument(): void
    {
        self::assertFalse(
            \isRoutableAction(ApplicationController::class, 'search')
        );

        self::assertTrue(
            \isRoutableAction(ApplicationController::class, 'search', ['term'])
        );

        self::assertTrue(
            \isRoutableAction(
                ApplicationController::class,
                'search',
                ['term', 'tag-a', 'tag-b']
            )
        );
    }

    public function testMissingControllerIsNotRoutable(): void
    {
        self::assertFalse(
            \isRoutableAction(
                'Loom73\\Tests\\Fixtures\\Routing\\MissingController',
                'index'
            )
        );
    }

    public function testMissingActionIsNotRoutable(): void
    {
        self::assertFalse(
            \isRoutableAction(ApplicationController::class, 'missingAction')
        );
    }
}
