<?php

declare(strict_types=1);

namespace Loom73\Tests\Unit\Heddle;

use Loom73\Beam\QueryResult;
use Loom73\Heddle\Auth;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Verifies authentication state and profile caching with controlled session results.
 * Only session retrieval is replaced; Auth's public methods execute normally.
 */
final class AuthStateTest extends TestCase
{
    public function testSuccessfulSessionAuthenticatesAndCachesTheProfile(): void
    {
        $profile = QueryResult::success(
            data: [(object) ['id' => 42, 'role' => 'user']]
        );
        $auth = $this->authWithSessionResults($profile);

        self::assertTrue($auth->isLoggedIn());
        self::assertTrue($auth->is_authenticated);
        self::assertSame($profile, $auth->getProfile());
    }

    public function testEmptySessionClearsAnEarlierAuthenticatedState(): void
    {
        $profile = QueryResult::success(
            data: [(object) ['id' => 42, 'role' => 'user']]
        );
        $empty = QueryResult::success();
        $auth = $this->authWithSessionResults($profile, $empty);

        self::assertTrue($auth->isLoggedIn());
        self::assertFalse($auth->isLoggedIn());
        self::assertFalse($auth->is_authenticated);
        self::assertSame($empty, $auth->getProfile());
    }

    public function testFailedSessionClearsAnEarlierAuthenticatedState(): void
    {
        $profile = QueryResult::success(
            data: [(object) ['id' => 42, 'role' => 'user']]
        );
        $failure = QueryResult::failure(
            new RuntimeException('Session unavailable.')
        );
        $auth = $this->authWithSessionResults($profile, $failure);

        self::assertTrue($auth->isLoggedIn());
        self::assertFalse($auth->isLoggedIn());
        self::assertFalse($auth->is_authenticated);
        self::assertSame($failure, $auth->getProfile());
    }

    public function testGetProfileCachesTheFirstSessionLookup(): void
    {
        $profile = QueryResult::success(
            data: [(object) ['id' => 42, 'role' => 'user']]
        );
        $auth = $this->authWithSessionResults($profile);

        self::assertSame($profile, $auth->getProfile());
        self::assertSame($profile, $auth->getProfile());
    }

    /**
     * Replaces session retrieval with the supplied sequence, preserving the constructor.
     */
    private function authWithSessionResults(QueryResult ...$results): Auth
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::never())->method('prepare');

        $auth = $this->getMockBuilder(Auth::class)
            ->setConstructorArgs([$pdo])
            ->onlyMethods(['getSession'])
            ->getMock();

        $auth->expects(self::exactly(count($results)))
            ->method('getSession')
            ->willReturnOnConsecutiveCalls(...$results);

        return $auth;
    }
}
