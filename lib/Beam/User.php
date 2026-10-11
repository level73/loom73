<?php

namespace Loom73\Beam;

use PDO;
use Throwable;

class User extends Model
{
    protected string $table = 'auth_user';

    protected bool $dates = true;

    protected string $pkey = 'idauth_user';

    protected bool $softDeletes = true;

    protected string $statusColumn = 'status';

    public function findByUsernameOrEmail(string $username): QueryResult
    {
        return $this->getBy(
            [
                'username' => [
                    'operator' => '=',
                    'value' => $username,
                    'type' => PDO::PARAM_STR,
                ],
                'email' => [
                    'operator' => '=',
                    'value' => $username,
                    'type' => PDO::PARAM_STR,
                ],
            ],
            'OR',
            1
        );
    }

    public function setRecoveryCode(QueryResult $user): string|false|Throwable
    {
        $profile = $user instanceof QueryResult
            ? $user->first()
            : $user;

        if (!$profile || empty($profile->idauth_user)) {
            return false;
        }

        try {
            $recoveryCode = strtoupper(bin2hex(random_bytes(16)));
        } catch (Throwable $e) {
            return $e;
        }

        $sql = '
            UPDATE ' . $this->tableName() . '
            SET `recovery` = :recovery_code,
                `recovery_created_at` = NOW()
            WHERE ' . $this->column($this->pkey) . ' = :id
        ';

        $update = $this->query($sql, [
            'id' => [
                'value' => (int) $profile->idauth_user,
                'type' => PDO::PARAM_INT,
            ],
            'recovery_code' => [
                'value' => $recoveryCode,
                'type' => PDO::PARAM_STR,
            ],
        ]);

        if ($update->passes()) {
            return $recoveryCode;
        }

        return $update->exception() ?? false;
    }

    public function allProfiles(): QueryResult
    {
        $sql = '
            SELECT 
                auth_user.idauth_user AS id,
                auth_user.username AS username,
                auth_user.email AS email,
                IF(auth_user.status = ' . STATUS_ACTIVE . ', "active", "inactive") AS status,
                a_r.role AS role,
                DATE_FORMAT(a_s.modified_at, "%a, %d/%m/%Y %T") AS last_access,
                DATE_FORMAT(auth_user.created_at, "%a, %d/%m/%Y") AS created_at,
                DATE_FORMAT(a_s.modified_at, "%Y-%m-%d %T") AS last_access_iso8601,
                DATE_FORMAT(auth_user.created_at, "%Y-%m-%d") AS created_at_iso8601
            FROM ' . $this->tableName() . '
            INNER JOIN `auth_role` AS a_r 
                ON a_r.idauth_role = auth_user.role
            LEFT JOIN `auth_session` AS a_s 
                ON a_s.auth_user = auth_user.idauth_user
            ORDER BY auth_user.created_at DESC
        ';

        return $this->query($sql);
    }

    public function oneProfile(int $id): QueryResult
    {
        $sql = '
            SELECT 
                auth_user.idauth_user AS id,
                auth_user.username AS username,
                auth_user.email AS email,
                IF(auth_user.status = ' . STATUS_ACTIVE . ', "active", "inactive") AS status,
                a_r.role AS role,
                DATE_FORMAT(a_s.modified_at, "%a, %d/%m/%Y %T") AS last_access,
                DATE_FORMAT(auth_user.created_at, "%a, %d/%m/%Y") AS created_at,
                DATE_FORMAT(a_s.modified_at, "%Y-%m-%d %T") AS last_access_iso8601,
                DATE_FORMAT(auth_user.created_at, "%Y-%m-%d") AS created_at_iso8601
            FROM ' . $this->tableName() . '
            INNER JOIN `auth_role` AS a_r 
                ON a_r.idauth_role = auth_user.role
            LEFT JOIN `auth_session` AS a_s 
                ON a_s.auth_user = auth_user.idauth_user
            WHERE auth_user.idauth_user = :id
            LIMIT 1
        ';

        return $this->query($sql, [
            'id' => [
                'value' => $id,
                'type' => PDO::PARAM_INT,
            ],
        ]);
    }
    /**-------- API MODELS --------**/
    public function apiList(): QueryResult
    {
        $sql = '
            SELECT
                u.username AS username,
                r.role AS role
            FROM ' . $this->tableName() . ' AS u
            LEFT JOIN `auth_role` AS r
                ON r.idauth_role = u.role
            WHERE u.status = ' . STATUS_ACTIVE . '
            ORDER BY u.username ASC';
        return $this->query($sql);
    }

    public function apiByUsername(string $username): QueryResult
    {
        $sql = "
        SELECT
            u.username,
            r.role AS role,
            u.modified_at AS modified_at
        FROM auth_user AS u
        LEFT JOIN auth_role AS r
            ON r.idauth_role = u.role
        WHERE u.username = :username
          AND u.status = 2
        LIMIT 1
    ";

        return $this->query(
            $sql, [ 'username' => $username ]
        );
    }
}