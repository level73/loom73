<?php

declare(strict_types=1);

namespace Loom73\Tests\Unit\Beam;

use Loom73\Beam\Connection;
use Loom73\Woodframe\Config;
use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;
use PDOException;
use RuntimeException;

/**
 * Verifies shared connection ownership without opening an actual DB connection
 * PDO stubs bypass the native constructor. Configuration is isolated for the test
 */

final class ConnectionTest extends TestCase
{
    protected function setUp(): void
    {
        Config::clear();
        Connection::reset();
        Config::set('database',
            [
                'default' => 'testing',
                'connections' => []
            ]
        );
    }

    protected function tearDown(): void
    {
        Connection::reset();
        Config::clear();
    }

    /**
     * An injected connection must be reused without going through configuration
     */
    public function testInjectedConnectionIsReused(): void
    {
        $pdo = $this->createStub(PDO::class);
        Connection::set($pdo);
        self::assertSame($pdo, Connection::pdo());
        self::assertSame($pdo, Connection::pdo());
    }
    public function testMissingConfigurationReportsTheSelectedConnection(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Database connection not configured: testing'
        );
        Connection::make();
    }

    /**
     * Reset must discard the shared PDO so the next access resolves the config
     */
    public function testResetDiscardsTheSharedConnection(): void
    {
        $pdo = $this->createStub(PDO::class);
        Connection::set($pdo);
        self::assertSame($pdo, Connection::pdo());

        Connection::reset();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Database connection not configured: testing'
        );
        Connection::pdo();
    }

    /** Statements & Queries **/

    /**
     * A successful health check executes SELECT 1 and releases its cursor.
     */
    public function testPingExecutesTheHealthQueryAndClosesItsCursor(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('execute')
            ->willReturn(true);
        $statement->expects(self::once())
            ->method('closeCursor')
            ->willReturn(true);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::once())
            ->method('prepare')
            ->with('SELECT 1')
            ->willReturn($statement);

        Connection::set($pdo);

        self::assertTrue(Connection::ping());
    }

    public function testPingReturnsFalseWhenPreparationFails(): void
    {
        $pdo = $this->createStub(PDO::class);
        $pdo->method('prepare')->willReturn(false);

        Connection::set($pdo);

        self::assertFalse(Connection::ping());
    }

    public function testPingClosesTheCursorWhenExecutionReturnsFalse(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('execute')
            ->willReturn(false);
        $statement->expects(self::once())
            ->method('closeCursor')
            ->willReturn(true);

        $pdo = $this->createStub(PDO::class);
        $pdo->method('prepare')->willReturn($statement);

        Connection::set($pdo);

        self::assertFalse(Connection::ping());
    }

    public function testPingReturnsFalseWhenPreparationThrows(): void
    {
        $pdo = $this->createStub(PDO::class);
        $pdo->method('prepare')->willThrowException(
            new PDOException('Preparation failed.')
        );

        Connection::set($pdo);

        self::assertFalse(Connection::ping());
    }

    public function testPingReturnsFalseWhenExecutionThrows(): void
    {
        $statement = $this->createStub(PDOStatement::class);
        $statement->method('execute')->willThrowException(
            new PDOException('Execution failed.')
        );

        $pdo = $this->createStub(PDO::class);
        $pdo->method('prepare')->willReturn($statement);

        Connection::set($pdo);

        self::assertFalse(Connection::ping());
    }

    public function testPingReturnsFalseWhenConnectionIsNotConfigured(): void
    {
        self::assertFalse(Connection::ping());
    }
}