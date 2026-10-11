<?php

namespace Loom73\Upgrade;

use PDO;

final class UpdateRegistry
{
    public const array COLUMNS = [
        'update_id',
        'kind',
        'loom73_version',
        'checksum_sha256',
        'applied_at_utc',
    ];

    public const string CREATE_SQL = "CREATE TABLE IF NOT EXISTS `core_updates` (
        `update_id` VARCHAR(160) NOT NULL,
        `kind` VARCHAR(16) NOT NULL,
        `loom73_version` VARCHAR(32) NOT NULL,
        `checksum_sha256` CHAR(64) DEFAULT NULL,
        `applied_at_utc` DATETIME NOT NULL,
        PRIMARY KEY (`update_id`)
    ) ENGINE=InnoDB
      DEFAULT CHARSET=utf8mb4
      COLLATE=utf8mb4_unicode_ci";


    public static function recordBaseline(PDO $pdo, string $version): void
    {
        $stmt = $pdo->prepare(
            "INSERT INTO `core_updates`
            (`update_id`, `kind`, `loom73_version`,
             `checksum_sha256`, `applied_at_utc`)
         VALUES
            ('baseline', 'baseline', :version, NULL, UTC_TIMESTAMP())"
        );

        $stmt->execute([':version' => $version]);
    }

    public static function recordScript(
        PDO $pdo,
        string $id,
        string $version,
        string $checksum
    ): void {
        $stmt = $pdo->prepare(
            "INSERT INTO `core_updates`
            (`update_id`, `kind`, `loom73_version`,
             `checksum_sha256`, `applied_at_utc`)
         VALUES
            (:id, 'schema', :version, :checksum, UTC_TIMESTAMP())"
        );

        $stmt->execute([
            ':id' => $id,
            ':version' => $version,
            ':checksum' => $checksum,
        ]);
    }
}