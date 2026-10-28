<?php


declare(strict_types=1);

namespace Loom73\Tests\Unit\Heddle;

use Loom73\Beam\QueryResult;
use Loom73\Heddle\Auth;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use PDO;

/**
 * Verifies role and ability decisions using supplied profiles.
 * No database connection or active PHP session is required.
 */
final class AuthTest extends TestCase
{
    private Auth $auth;

    protected function setUp(): void
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::never())->method('prepare');

        $this->auth = new Auth($pdo);
    }

    public function testRoleChecksAcceptObjectsAndQueryResults(): void
    {
        $admin = (object)['role' => 'admin'];
        $result = QueryResult::success(data: [$admin]);

        self::assertTrue($this->auth->hasRole($admin, 'admin'));
        self::assertTrue($this->auth->hasRole($result, ['editor', 'admin']));
        self::assertFalse($this->auth->hasRole($admin, ['editor', 'user']));
        self::assertFalse(
            $this->auth->hasRole((object)['role' => 'Admin'], 'admin')
        );
    }

    public function testMissingProfilesDoNotGrantRoles(): void
    {
        $failure = QueryResult::failure(
            new RuntimeException('Session unavailable.')
        );

        self::assertFalse($this->auth->hasRole(null, 'admin'));
        self::assertFalse($this->auth->hasRole((object)[], 'admin'));
        self::assertFalse(
            $this->auth->hasRole(QueryResult::success(), 'admin')
        );
        self::assertFalse($this->auth->hasRole($failure, 'admin'));
    }

    public function testAdminCheckRejectsOtherRoles(): void
    {
        self::assertTrue(
            $this->auth->isAdmin((object)['role' => 'admin'])
        );
        self::assertFalse(
            $this->auth->isAdmin((object)['role' => 'editor'])
        );
    }

    /**
     * Permissions follow the declared role map; unknown abilities grant nothing.
     */
    public function testAbilitiesRespectRoleMappingAndRejectUnknownNames(): void
    {
        $admin = (object)['role' => 'admin'];
        $editor = (object)['role' => 'editor'];
        $user = QueryResult::success(
            data: [(object)['role' => 'user']]
        );

        self::assertTrue($this->auth->can($admin, 'manage_users'));
        self::assertFalse($this->auth->can($editor, 'manage_users'));
        self::assertTrue($this->auth->can($editor, 'edit_content'));
        self::assertFalse($this->auth->can($user, 'edit_content'));
        self::assertTrue($this->auth->can($user, 'view_content'));
        self::assertFalse($this->auth->can($admin, 'unknown_ability'));
        self::assertFalse($this->auth->can(null, 'view_content'));
    }
}
