<?php

namespace Loom73\Heddle;

use Loom73\Beam\QueryResult;

/**
 * Handles authentication and permissions.
 */
class Auth extends Session
{
    public bool $is_authenticated = false;

    protected ?QueryResult $profile = null;

    private array $abilities = [
        'manage_users' => ['admin'],
        'edit_content' => ['admin', 'editor'],
        'view_content' => ['admin', 'editor', 'user'],
    ];

    public function isLoggedIn(): bool
    {
        $session = $this->getSession();

        $this->profile = $session;

        $this->is_authenticated = $session->passes() && $session->hasData();

        return $this->is_authenticated;
    }

    public function authorize(int $user): QueryResult
    {
        $token = $this->token();

        $result = $this->setSession($user, $token);

        if ($result->passes()) {
            $this->is_authenticated = true;
            $this->profile = $this->getSession();
        }

        return $result;
    }

    public function getProfile(): QueryResult
    {
        if ($this->profile instanceof QueryResult) {
            return $this->profile;
        }

        $this->profile = $this->getSession();

        return $this->profile;
    }


    protected function profileFrom(?object $user): ?object
    {
        if ($user instanceof QueryResult) {
            return $user->first();
        }

        return $user;
    }

    public function hasRole(?object $user, string|array $roles): bool
    {
        $profile = $this->profileFrom($user);

        if (!$profile) {
            return false;
        }

        $roles = is_array($roles) ? $roles : [$roles];

        return in_array((string) ($profile->role ?? ''), $roles, true);
    }


    public function isAdmin(?object $user): bool
    {
        return $this->hasRole($user, 'admin');
    }

    public function can(?object $user, string $ability): bool
    {
        if (!isset($this->abilities[$ability])) {
            return false;
        }

        return $this->hasRole($user, $this->abilities[$ability]);
    }

    private function token(): string
    {
        $salt = '$1$'
            . bin2hex(random_bytes(8))
            . ($_SERVER['SESSION_SALT'] ?? '');

        $token = md5(($_SERVER['APPNAME'] ?? 'Loom73') . bin2hex(random_bytes(8)));

        return crypt($token, $salt);
    }
}