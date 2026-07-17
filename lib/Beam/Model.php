<?php
/**
 * Base data-access layer for Loom73 owner models.
 *
 * Model intentionally provides only a small CRUD surface and a protected
 * query() method. It does not attempt to hide SQL or replace it with a
 * generic query builder.
 *
 * Concrete models should use the CRUD helpers for ordinary operations and
 * write explicit SQL for domain-specific reads, reports, joins, unions,
 * aggregations, and performance-sensitive queries.
 */

namespace Loom73\Beam;

use InvalidArgumentException;
use PDO;
use PDOException;

abstract class Model
{
    protected PDO $pdo;

    protected string $table;

    protected string $pkey;

    protected bool $dates = true;

    protected array $modelErrorCodes = [
        1062 => 'Duplicate entry.',
    ];

    protected const int STATUS_INACTIVE = 1;
    protected const int STATUS_ACTIVE = 2;
    protected bool $softDeletes = false;

    protected string $statusColumn = 'status';

    public function __construct(?PDO $pdo = null)
    {
        if (empty($this->table)) {
            throw new InvalidArgumentException('Model table name is not defined.');
        }

        if (empty($this->pkey)) {
            $this->pkey = 'id' . $this->table;
        }

        $this->pdo = $pdo ?? Connection::pdo();
    }

    protected function query(
        string $sql,
        array $params = [],
        bool $insert = false
    ): QueryResult {
        if (($_SERVER['DEBUG_SQL'] ?? 0) == 1 && function_exists('prettySQL')) {
            prettySQL($sql);
        }

        try {
            $statement = $this->pdo->prepare($sql);

            foreach ($params as $key => $param) {
                $placeholder = ':' . ltrim((string) $key, ':');

                if (is_array($param) && array_key_exists('value', $param)) {
                    $value = $param['value'];
                    $type = $param['type'] ?? $this->guessPdoType($value);
                } else {
                    $value = $param;
                    $type = $this->guessPdoType($value);
                }

                $statement->bindValue($placeholder, $value, $type);
            }

            $statement->execute();

            if ($insert) {
                return QueryResult::success(
                    rows: $statement->rowCount(),
                    insertId: (int) $this->pdo->lastInsertId()
                );
            }

            return QueryResult::success(
                rows: $statement->rowCount(),
                data: $statement->fetchAll(PDO::FETCH_OBJ)
            );
        } catch (PDOException $e) {
            return QueryResult::failure($e);
        }
    }

    protected function guessPdoType(mixed $value): int
    {
        return match (true) {
            is_int($value) => PDO::PARAM_INT,
            is_bool($value) => PDO::PARAM_BOOL,
            $value === null => PDO::PARAM_NULL,
            default => PDO::PARAM_STR,
        };
    }

    public function getById(int $id): QueryResult
    {
        $sql = 'SELECT * FROM '
            . $this->tableName()
            . ' WHERE '
            . $this->column($this->pkey)
            . ' = :id LIMIT 1';

        return $this->query($sql, [
            'id' => [
                'value' => $id,
                'type' => PDO::PARAM_INT,
            ],
        ]);
    }

    public function getBy(
        array $conditions,
        string $boolean = 'AND',
        ?int $limit = null,
        ?string $orderBy = null,
        string $direction = 'ASC'
    ): QueryResult {
        $where = $this->buildWhere($conditions, $boolean);

        $sql = 'SELECT * FROM '
            . $this->tableName()
            . ' WHERE '
            . $where['sql'];

        if ($orderBy !== null) {
            $sql .= ' ORDER BY '
                . $this->column($orderBy)
                . ' '
                . $this->direction($direction);
        }

        if ($limit !== null) {
            $sql .= ' LIMIT ' . max(1, $limit);
        }

        return $this->query($sql, $where['params']);
    }

    public function all(
        ?string $orderBy = null,
        string $direction = 'ASC'
    ): QueryResult {
        $sql = 'SELECT * FROM ' . $this->tableName();

        $sql .= ' ORDER BY '
            . $this->column($orderBy ?? $this->pkey)
            . ' '
            . $this->direction($direction);

        return $this->query($sql);
    }

    public function create(array $data): QueryResult
    {
        if (empty($data)) {
            throw new InvalidArgumentException('Create data cannot be empty.');
        }

        $fields = array_keys($data);

        $columns = array_map(
            fn (string $field) => $this->column($field),
            $fields
        );

        $placeholders = array_map(
            fn (string $field) => ':' . $field,
            $fields
        );

        $sql = 'INSERT INTO '
            . $this->tableName()
            . ' ('
            . implode(', ', $columns)
            . ') VALUES ('
            . implode(', ', $placeholders)
            . ')';

        return $this->query($sql, $data, true);
    }


    /*
    |--------------------------------------------------------------------------
    | Updates
    |--------------------------------------------------------------------------
    |
    | updateById() is the default path and should be used for ordinary record
    | updates from controllers.
    |
    | updateWhere() supports structured, bound conditions for batch operations
    | or application-level updates that affect records by metadata.
    |
    | updateRawWhere() is an explicit escape hatch for advanced cases. The raw
    | SQL fragment must never contain user input directly; dynamic values should
    | always be passed through bound parameters.
    |
    */

    public function updateById(array $data, int $id): QueryResult
    {
        return $this->updateWhere($data, [
            $this->pkey => [
                'operator' => '=',
                'value' => $id,
                'type' => PDO::PARAM_INT,
            ],
        ]);
    }

    public function updateWhere(
        array $data,
        array $conditions,
        string $boolean = 'AND'
    ): QueryResult {
        if (empty($data)) {
            throw new InvalidArgumentException('Update data cannot be empty.');
        }

        if (empty($conditions)) {
            throw new InvalidArgumentException('Update conditions cannot be empty.');
        }

        $set = $this->buildSet($data);
        $where = $this->buildWhere($conditions, $boolean);

        $sql = 'UPDATE '
            . $this->tableName()
            . ' SET '
            . $set['sql']
            . ' WHERE '
            . $where['sql'];

        return $this->query(
            $sql,
            array_merge($set['params'], $where['params'])
        );
    }

    public function updateRawWhere(
        array $data,
        string $whereSql,
        array $whereParams = []
    ): QueryResult {
        if (empty($data)) {
            throw new InvalidArgumentException('Update data cannot be empty.');
        }

        if (trim($whereSql) === '') {
            throw new InvalidArgumentException('Raw WHERE clause cannot be empty.');
        }

        $set = $this->buildSet($data);

        $params = $set['params'];

        foreach ($whereParams as $key => $param) {
            $params[$key] = $this->normalizeParam($param);
        }

        $sql = 'UPDATE '
            . $this->tableName()
            . ' SET '
            . $set['sql']
            . ' WHERE '
            . $whereSql;

        return $this->query($sql, $params);
    }

    public function update(array $data, int|array|string|null $conditions): QueryResult
    {
        if (is_int($conditions)) {
            return $this->updateById($data, $conditions);
        }

        if (is_array($conditions)) {
            return $this->updateWhere($data, $conditions);
        }

        if (is_string($conditions)) {
            return $this->updateRawWhere($data, $conditions);
        }

        throw new InvalidArgumentException('Update conditions are required.');
    }

    public function deleteById(int $id): QueryResult
    {
        $sql = 'DELETE FROM '
            . $this->tableName()
            . ' WHERE '
            . $this->column($this->pkey)
            . ' = :id';

        return $this->query($sql, [
            'id' => [
                'value' => $id,
                'type' => PDO::PARAM_INT,
            ],
        ]);
    }

    public function softDeleteById(int $id): QueryResult
    {
        if (!$this->softDeletes) {
            throw new InvalidArgumentException(
                "Model {$this->table} does not support soft deletes."
            );
        }

        return $this->updateById([
            $this->statusColumn => [
                'value' => self::STATUS_INACTIVE,
                'type' => PDO::PARAM_INT,
            ],
        ], $id);
    }

    public function restoreById(int $id): QueryResult
    {
        if (!$this->softDeletes) {
            throw new InvalidArgumentException(
                "Model {$this->table} does not support soft deletes."
            );
        }

        return $this->updateById([
            $this->statusColumn => [
                'value' => self::STATUS_ACTIVE,
                'type' => PDO::PARAM_INT,
            ],
        ], $id);
    }

    public function getFields(): array
    {
        $sql = 'SHOW COLUMNS FROM ' . $this->tableName();

        $statement = $this->pdo->prepare($sql);
        $statement->execute();

        $fields = [];

        foreach ($statement->fetchAll(PDO::FETCH_OBJ) as $field) {
            $type = strtoupper((string) $field->Type);

            $fields[] = [
                'name' => $field->Field,
                'type' => $this->pdoTypeFromColumnType($type),
            ];
        }

        return $fields;
    }

    protected function buildSet(array $data): array
    {
        $parts = [];
        $params = [];

        foreach ($data as $field => $value) {
            $placeholder = 'set_' . $field;

            $parts[] = $this->column($field) . ' = :' . $placeholder;

            $params[$placeholder] = $this->normalizeParam($value);
        }

        return [
            'sql' => implode(', ', $parts),
            'params' => $params,
        ];
    }

    protected function buildWhere(
        array $conditions,
        string $boolean = 'AND'
    ): array {
        $boolean = strtoupper($boolean);

        if (!in_array($boolean, ['AND', 'OR'], true)) {
            throw new InvalidArgumentException("Unsupported boolean operator: {$boolean}");
        }

        $parts = [];
        $params = [];

        foreach ($conditions as $field => $condition) {
            if (is_array($condition) && array_key_exists('value', $condition)) {
                $operator = strtoupper($condition['operator'] ?? '=');
                $value = $condition['value'];
                $type = $condition['type'] ?? $this->guessPdoType($value);
            } else {
                $operator = '=';
                $value = $condition;
                $type = $this->guessPdoType($value);
            }

            if (!in_array($operator, $this->allowedOperators(), true)) {
                throw new InvalidArgumentException("Unsupported SQL operator: {$operator}");
            }

            $placeholder = 'where_' . $field;

            if ($operator === 'IS NULL' || $operator === 'IS NOT NULL') {
                $parts[] = $this->column($field) . ' ' . $operator;
                continue;
            }

            $parts[] = $this->column($field) . ' ' . $operator . ' :' . $placeholder;

            $params[$placeholder] = [
                'value' => $value,
                'type' => $type,
            ];
        }

        return [
            'sql' => implode(' ' . $boolean . ' ', $parts),
            'params' => $params,
        ];
    }

    protected function normalizeParam(mixed $param): array
    {
        if (is_array($param) && array_key_exists('value', $param)) {
            $value = $param['value'];

            return [
                'value' => $value,
                'type' => $param['type'] ?? $this->guessPdoType($value),
            ];
        }

        return [
            'value' => $param,
            'type' => $this->guessPdoType($param),
        ];
    }

    protected function allowedOperators(): array
    {
        return [
            '=',
            '!=',
            '<>',
            '>',
            '>=',
            '<',
            '<=',
            'LIKE',
            'NOT LIKE',
            'IS NULL',
            'IS NOT NULL',
        ];
    }

    protected function pdoTypeFromColumnType(string $type): int
    {
        return match (true) {
            str_starts_with($type, 'INT'),
            str_starts_with($type, 'BIGINT'),
            str_starts_with($type, 'SMALLINT'),
            str_starts_with($type, 'TINYINT'),
            str_starts_with($type, 'MEDIUMINT') => PDO::PARAM_INT,

            default => PDO::PARAM_STR,
        };
    }

    protected function tableName(): string
    {
        return $this->identifier($this->table);
    }

    protected function column(string $name): string
    {
        return $this->identifier($name);
    }

    protected function identifier(string $identifier): string
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $identifier)) {
            throw new InvalidArgumentException("Invalid SQL identifier: {$identifier}");
        }

        return '`' . $identifier . '`';
    }

    protected function direction(string $direction): string
    {
        $direction = strtoupper($direction);

        if (!in_array($direction, ['ASC', 'DESC'], true)) {
            throw new InvalidArgumentException("Invalid SQL direction: {$direction}");
        }

        return $direction;
    }
}