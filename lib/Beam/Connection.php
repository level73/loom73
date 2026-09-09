<?php

namespace Loom73\Beam;

use Loom73\Woodframe\Config;
use PDO;
use PDOException;
use RuntimeException;

class Connection
{
    protected static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        self::$pdo = self::make();

        return self::$pdo;
    }

    public static function make(?array $config = null): PDO
    {

        $connection = Config::get('database.default', 'mysql');

        $config ??= Config::get("database.connections.{$connection}");

        if (!$config) {
            throw new RuntimeException("Database connection not configured: {$connection}");
        }

        $driver = $config['driver'] ?? 'mysql';
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? '3306';
        $database = $config['database'] ?? 'Loom73';
        $charset = $config['charset'] ?? 'utf8mb4';

        $dsn = "{$driver}:dbname={$database};host={$host};port={$port};charset={$charset}";

        try {
            $pdo = new PDO(
                $dsn,
                $config['username'] ?? '',
                $config['password'] ?? '',
                [
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                ]
            );

            $timezone = $config['timezone'] ?? '+00:00';

            if ($timezone) {
                $pdo->exec("SET time_zone='{$timezone}';");
            }

            return $pdo;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public static function set(PDO $pdo): void
    {
        self::$pdo = $pdo;
    }

    public static function reset(): void
    {
        self::$pdo = null;
    }

    /**
     * This method can be used to check
     * if the DB is active mocking a model
     * @return bool
     */
    public static function ping(): bool
    {
        try {
            $stmt = self::pdo()->prepare('SELECT 1');

            if ($stmt === false):
                return false;
            endif;

            $success = $stmt->execute();
            $stmt->closeCursor();

            return $success;
        } catch (RuntimeException) {
            return false;
        }
    }
}