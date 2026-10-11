<?php

namespace Loom73\Upgrade;

use PDO;

final class SchemaInspector
{
    /**
     * @return array{errors: list<string>, warnings: list<string>}
     */
    public static function inspectBaseline62(PDO $pdo): array
    {
        $tables = $pdo->query(
            'SELECT TABLE_NAME, TABLE_TYPE
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()'
        )->fetchAll(PDO::FETCH_KEY_PAIR);

        $rows = $pdo->query(
            'SELECT TABLE_NAME, COLUMN_NAME
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()'
        )->fetchAll(PDO::FETCH_ASSOC);

        $columns = [];

        foreach ($rows as $row):
            $columns[$row['TABLE_NAME']][] = $row['COLUMN_NAME'];
        endforeach;

        $errors = [];
        $warnings = [];

        foreach (SchemaBaseline62::REQUIRED_COLUMNS as $table => $required):
            if (!array_key_exists($table, $tables)):
                $errors[] = "Missing core table: {$table}";
                continue;
            endif;

            if ($tables[$table] !== 'BASE TABLE'):
                $errors[] = "Core object is not a table: {$table}";
                continue;
            endif;

            $actual = $columns[$table] ?? [];

            foreach (array_diff($required, $actual) as $column):
                $errors[] = "Missing core column: {$table}.{$column}";
            endforeach;

            foreach (array_diff($actual, $required) as $column):
                $warnings[] = "Additional column in core table: {$table}.{$column}";
            endforeach;
        endforeach;

        $rows = $pdo->query(
            'SELECT k.TABLE_NAME, k.CONSTRAINT_NAME, k.COLUMN_NAME,
                            k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME,
                            c.CONSTRAINT_TYPE
                     FROM information_schema.KEY_COLUMN_USAGE AS k
                     JOIN information_schema.TABLE_CONSTRAINTS AS c
                       ON c.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA
                      AND c.TABLE_NAME = k.TABLE_NAME
                      AND c.CONSTRAINT_NAME = k.CONSTRAINT_NAME
                     WHERE k.TABLE_SCHEMA = DATABASE()
                     ORDER BY k.TABLE_NAME, k.CONSTRAINT_NAME, k.ORDINAL_POSITION'
        )->fetchAll(PDO::FETCH_ASSOC);

        $constraints = [];

        foreach ($rows as $row):
            $table = $row['TABLE_NAME'];
            $name = $row['CONSTRAINT_NAME'];

            $constraints[$table][$name] ??= [
                'type' => $row['CONSTRAINT_TYPE'],
                'columns' => [],
                'references' => [],
            ];

            $constraints[$table][$name]['columns'][] = $row['COLUMN_NAME'];

            if ($row['REFERENCED_TABLE_NAME'] !== null):
                $constraints[$table][$name]['references'][] =
                    $row['REFERENCED_TABLE_NAME'] . '.' . $row['REFERENCED_COLUMN_NAME'];
            endif;
        endforeach;

        foreach (SchemaBaseline62::PRIMARY_KEYS as $table => $column):
            if (($tables[$table] ?? null) !== 'BASE TABLE'):
                continue;
            endif;

            if (!self::hasConstraint($constraints[$table] ?? [], 'PRIMARY KEY', [$column])):
                $errors[] = "Missing primary key: {$table}.{$column}";
            endif;
        endforeach;

        foreach (SchemaBaseline62::UNIQUE_COLUMNS as $table => $uniqueColumns):
            if (($tables[$table] ?? null) !== 'BASE TABLE'):
                continue;
            endif;

            foreach ($uniqueColumns as $column):
                if (!self::hasConstraint($constraints[$table] ?? [], 'UNIQUE', [$column])):
                    $errors[] = "Missing unique constraint: {$table}.{$column}";
                endif;
            endforeach;
        endforeach;

        foreach (SchemaBaseline62::FOREIGN_KEYS as $source => $target):
            [$table, $column] = explode('.', $source, 2);

            if (($tables[$table] ?? null) !== 'BASE TABLE'):
                continue;
            endif;

            if (!self::hasConstraint(
                $constraints[$table] ?? [],
                'FOREIGN KEY',
                [$column],
                [$target]
            )):
                $errors[] = "Missing foreign key: {$source} → {$target}";
            endif;
        endforeach;

        return ['errors' => $errors, 'warnings' => $warnings];
    }

    private static function hasConstraint(
        array $constraints,
        string $type,
        array $columns,
        array $references = []
    ): bool {
        foreach ($constraints as $constraint):
            if (
                $constraint['type'] === $type
                && $constraint['columns'] === $columns
                && $constraint['references'] === $references
            ):
                return true;
            endif;
        endforeach;

        return false;
    }
}