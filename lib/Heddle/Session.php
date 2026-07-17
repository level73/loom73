<?php

namespace Loom73\Heddle;

use Loom73\Beam\Model;
use Loom73\Beam\QueryResult;
use PDO;
use Random\RandomException;

class Session extends Model
{
    protected string $table = 'auth_session';

    protected string $pkey = 'idauth_session';

    protected bool $dates = true;

    /**
     * Create a placeholder session row attached to a user.
     *
     * @throws RandomException
     */
    public function createSession(int $userId): QueryResult
    {
        $token = bin2hex(random_bytes(16));

        return $this->create([
            'session' => [
                'value' => '$noToken$' . $token,
                'type' => PDO::PARAM_STR,
            ],
            'auth_user' => [
                'value' => $userId,
                'type' => PDO::PARAM_INT,
            ],
        ]);
    }

    /**
     * Update session token for a user and mirror it into $_SESSION.
     */
    public function setSession(int $userId, string $sessionToken): QueryResult
    {
        $sql = '
            UPDATE ' . $this->tableName() . '
            SET `session` = :session
            WHERE `auth_user` = :auth_user
        ';

        $result = $this->query($sql, [
            'session' => [
                'value' => $sessionToken,
                'type' => PDO::PARAM_STR,
            ],
            'auth_user' => [
                'value' => $userId,
                'type' => PDO::PARAM_INT,
            ],
        ]);

        if ($result->passes()) {
            $this->storeSessionToken($sessionToken);
        }

        return $result;
    }

    /**
     * Retrieve current authenticated session info.
     */
    protected function getSession(): QueryResult
    {
        if (!$this->checkKey()) {
            return QueryResult::success();
        }

        $session = $_SESSION[$_SERVER['APPNAME']][$_SERVER['SESSION_KEY']];

        $sql = '
            SELECT 
                auth_user.idauth_user AS id,
                auth_user.username,
                auth_user.email,
                auth_user.role AS role_id,
                auth_role.role AS role
            FROM `auth_user`
            INNER JOIN `auth_session`
                ON auth_session.auth_user = auth_user.idauth_user
            INNER JOIN `auth_role` 
                ON auth_role.idauth_role = auth_user.role
            WHERE auth_session.session = :session
            LIMIT 1
        ';

        return $this->query($sql, [
            'session' => [
                'value' => $session,
                'type' => PDO::PARAM_STR,
            ],
        ]);
    }

    protected function storeSessionToken(string $sessionToken): void
    {
        $appName = $_SERVER['APPNAME'] ?? null;
        $sessionKey = $_SERVER['SESSION_KEY'] ?? null;

        if (!$appName || !$sessionKey) {
            return;
        }

        if (!isset($_SESSION[$appName]) || !is_array($_SESSION[$appName])) {
            $_SESSION[$appName] = [];
        }

        $_SESSION[$appName][$sessionKey] = $sessionToken;
    }

    private function checkKey(): bool
    {
        $appName = $_SERVER['APPNAME'] ?? null;
        $sessionKey = $_SERVER['SESSION_KEY'] ?? null;

        if (!$appName || !$sessionKey) {
            return false;
        }

        if (!isset($_SESSION[$appName]) || !is_array($_SESSION[$appName])) {
            return false;
        }

        return array_key_exists($sessionKey, $_SESSION[$appName]);
    }
}