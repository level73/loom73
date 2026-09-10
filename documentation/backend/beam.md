<!-- FILE: documentation/backend/beam.md -->

# Beam

Beam is Loom73's database-access layer.

It provides a shared PDO connection, a small base model and a normalized result object. It reduces repetition without hiding SQL.

```text
Connection   create and share PDO connections
Model        execute prepared statements and common CRUD
QueryResult  describe database outcomes
```

Beam is not an ORM and does not provide relationships, migrations, automatic serialization or a general query builder.

## Connection

`Loom73\Beam\Connection` creates PDO connections from the `database` configuration module.

### Shared connection

```php
use Loom73\Beam\Connection;

$pdo = Connection::pdo();
```

The first call creates the PDO instance. Later calls return the same instance for the current PHP process.

### Independent connection

```php
$pdo = Connection::make([
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'example',
    'username' => 'example_user',
    'password' => 'secret',
    'charset' => 'utf8mb4',
    'timezone' => '+00:00',
]);
```

Without an argument, `make()` reads:

```text
database.default
database.connections.{connection}
```

Default PDO attributes are:

```php
PDO::ATTR_EMULATE_PREPARES => false;
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION;
PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ;
```

Database configuration and timezone are trusted operational configuration. Do not construct them from request data.

### Replacing the shared connection

Tests and controlled bootstrap code can replace or clear the connection:

```php
Connection::set($pdo);
Connection::reset();
```

After `reset()`, the next `pdo()` call creates a new configured connection.

### Health check

```php
if (!Connection::ping()):
    // The database is unavailable.
endif;
```

`ping()` prepares and executes:

```sql
SELECT 1
```

It returns a boolean and closes the statement cursor. It is intended for operational checks such as `php shuttle loom73.info`.

Connection failures from `pdo()` and `make()` throw exceptions. `ping()` converts connection or statement failures into `false`.

## Model

Concrete database models extend `Loom73\Beam\Model`.

```php
namespace Loom73\Beam;

class Article extends Model
{
    protected string $table = 'articles';
    protected string $pkey = 'idarticle';

    protected bool $softDeletes = true;
    protected string $statusColumn = 'status';
}
```

A table name is required. Constructing a model with an empty table name throws `InvalidArgumentException`.

If the primary key is empty, Model derives:

```text
id{table}
```

### Injecting PDO

Models use the shared connection by default:

```php
$Article = new Article();
```

A specific PDO instance can be supplied:

```php
$Article = new Article($pdo);
```

This is useful for tests and deliberate connection boundaries.

### Public methods

| Method | Purpose |
| --- | --- |
| `getById(int $id)` | Retrieve one record by primary key. |
| `getBy(array $conditions, string $boolean = 'AND', ?int $limit = null, ?string $orderBy = null, string $direction = 'ASC')` | Retrieve records using structured conditions. |
| `all(?string $orderBy = null, string $direction = 'ASC')` | Retrieve every record. |
| `create(array $data)` | Insert one record. |
| `updateById(array $data, int $id)` | Update one primary-key record. |
| `updateWhere(array $data, array $conditions, string $boolean = 'AND')` | Update records using structured conditions. |
| `updateRawWhere(array $data, string $whereSql, array $whereParams = [])` | Update with an explicit WHERE fragment. |
| `update(array $data, int\|array\|string\|null $conditions)` | Compatibility dispatcher for the three update forms. |
| `deleteById(int $id)` | Permanently delete a record. |
| `softDeleteById(int $id)` | Set the configured status column to inactive. |
| `restoreById(int $id)` | Set the configured status column to active. |
| `getFields()` | Read table column names and basic PDO types. |

These methods return `QueryResult`, except invalid arguments and `getFields()` database failures, which may throw.

### Creating records

Values can be passed directly:

```php
$result = $Article->create([
    'title' => 'First article',
    'status' => 2,
]);
```

Explicit PDO types can be supplied:

```php
use PDO;

$result = $Article->create([
    'title' => [
        'value' => 'First article',
        'type' => PDO::PARAM_STR,
    ],
    'status' => [
        'value' => 2,
        'type' => PDO::PARAM_INT,
    ],
]);
```

Model infers ordinary scalar types as:

```text
integer  → PDO::PARAM_INT
boolean  → PDO::PARAM_BOOL
null     → PDO::PARAM_NULL
other    → PDO::PARAM_STR
```

Empty create or update data throws `InvalidArgumentException`.

### Structured conditions

A direct value uses equality:

```php
$result = $Article->getBy([
    'status' => 2,
]);
```

An explicit operator uses:

```php
$result = $Article->getBy(
    conditions: [
        'published_at' => [
            'operator' => 'IS NOT NULL',
            'value' => null,
        ],
        'title' => [
            'operator' => 'LIKE',
            'value' => '%Loom73%',
        ],
    ],
    boolean: 'AND',
    limit: 20,
    orderBy: 'created_at',
    direction: 'DESC'
);
```

Supported operators are:

```text
=
!=
<>
>
>=
<
<=
LIKE
NOT LIKE
IS NULL
IS NOT NULL
```

The boolean connector must be `AND` or `OR`.

`IN`, `BETWEEN`, groups and nested conditions are not part of the structured condition API. Write explicit SQL for those cases.

### Updating records

Prefer the narrowest method:

```php
$result = $Article->updateById(
    [
        'title' => 'Updated title',
    ],
    $articleId
);
```

Batch updates can use structured conditions:

```php
$result = $Article->updateWhere(
    [
        'status' => 1,
    ],
    [
        'published_at' => [
            'operator' => 'IS NULL',
            'value' => null,
        ],
    ]
);
```

`updateRawWhere()` is an explicit escape hatch:

```php
$result = $Article->updateRawWhere(
    data: [
        'status' => 1,
    ],
    whereSql: '`expires_at` < :now',
    whereParams: [
        'now' => $timestamp,
    ]
);
```

The raw SQL fragment must be written by the application. Never concatenate request data into it.

Empty update conditions are rejected by `updateWhere()`.

### Identifiers

Table names, column names and ordering columns cannot be bound as SQL values.

Model validates them against:

```text
letters
numbers
underscore
```

It then wraps them in backticks.

Invalid identifiers and sort directions throw `InvalidArgumentException`.

This validation allows application-defined identifiers. It does not make request-selected columns automatically appropriate. Prefer application allowlists before passing a requested field name.

### Explicit SQL

Concrete models can call the protected `query()` method:

```php
public function publishedWithAuthor(): QueryResult
{
    $sql = '
        SELECT
            article.title,
            author.name AS author
        FROM article
        INNER JOIN author
            ON author.idauthor = article.author
        WHERE article.status = :status
        ORDER BY article.created_at DESC
    ';

    return $this->query($sql, [
        'status' => 2,
    ]);
}
```

Use explicit SQL for:

```text
joins
unions
aggregations
subqueries
reports
domain-specific projections
performance-sensitive reads
```

Values remain bound parameters.

### Dates

Concrete models may declare:

```php
protected bool $dates = true;
```

The current base Model does not automatically add or update timestamp fields. Database defaults or explicit model queries currently own timestamp behavior.

### Soft deletes

Enable helpers with:

```php
protected bool $softDeletes = true;
protected string $statusColumn = 'status';
```

Loom73 uses:

```text
1  inactive
2  active
```

Then call:

```php
$result = $Article->softDeleteById($id);
$result = $Article->restoreById($id);
```

Soft-delete support only changes the status value. `getById()`, `getBy()` and `all()` do not automatically exclude inactive rows.

Concrete read methods must add the required status condition.

### Transactions

Model does not create implicit transactions.

When several writes must succeed or fail together, use the model's PDO connection from an appropriate model/service implementation and make the transaction visible in that operation.

## QueryResult

`QueryResult` separates three common states:

```text
failure
successful query with no data
successful query with data
```

Example:

```php
$result = $Article->getById($id);

if ($result->fails()):
    Logger::error(
        'Article',
        'Unable to retrieve article',
        [
            'error' => $result->errorMessage(),
        ]
    );

    return;
endif;

if ($result->isEmpty()):
    // No article exists for this ID.
    return;
endif;

$article = $result->first();
```

### Properties

```php
$result->success;
$result->rows;
$result->data;
$result->insertId;
$result->error;
```

All properties are readonly.

### Methods

| Method | Meaning |
| --- | --- |
| `passes()` | The database operation completed successfully. |
| `fails()` | The database operation failed. |
| `first()` | First returned object or `null`. |
| `all()` | All returned objects. |
| `count()` | Number of objects in `data`. |
| `hasData()` | Whether `data` contains records. |
| `isEmpty()` | Whether `data` is empty. |
| `map(callable $callback)` | Maps returned objects. |
| `pluck(string $property)` | Extracts one property from every object. |
| `errorMessage()` | Exception message or `null`. |
| `errorCode()` | Exception code or `null`. |
| `databaseErrorCode()` | Driver error code when available. |
| `exception()` | Original exception or `null`. |

For returned datasets, prefer `count()` when the number of fetched objects matters. The `rows` property comes from `PDOStatement::rowCount()` and is most reliable for affected rows.

### Failure boundary

PDO exceptions inside `Model::query()` become failed `QueryResult` objects.

Invalid identifiers, unsupported operators, empty mutation data and other invalid API usage throw `InvalidArgumentException`.

This distinction is intentional:

```text
QueryResult failure
    expected database-operation outcome

exception
    invalid use, missing configuration or programming error
```
