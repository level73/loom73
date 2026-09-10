<!-- FILE: documentation/backend/index.md -->

# Loom73 Backend

The Loom73 backend is organized by responsibility.

Each core namespace owns one recurring application concern. Application controllers combine those components into concrete behavior without hiding the request flow.

```text
Woodframe  application structure and shared services
Beam       database access
Heddle     authentication and authorization
Yarn       uploaded assets
Ledger     audit trail
Weave      application controllers and responses
Shuttle    command-line operations
```

This reference follows the namespaces rather than creating one page for every class. Each namespace page contains a class index and stable headings for individual class contracts.

## Find documentation by task

| I need to… | Read |
| --- | --- |
| Read configuration or define a module | [Woodframe: Config](woodframe.md#config) |
| Create a controller or handle a request | [Woodframe: Ctrl](woodframe.md#ctrl) and [MVC](../mvc.md) |
| Render a view | [Woodframe: Template](woodframe.md#template) |
| Redirect with user feedback | [Woodframe: Flash](woodframe.md#flash) |
| Write an operational error | [Woodframe: Logger](woodframe.md#logger) |
| Send email | [Woodframe: Mailman](woodframe.md#mailman) |
| Register application owner types | [Woodframe: OwnerRegistry](woodframe.md#ownerregistry) |
| Connect to the database | [Beam: Connection](beam.md#connection) |
| Create a model or execute SQL | [Beam: Model](beam.md#model) |
| Interpret a database result | [Beam: QueryResult](beam.md#queryresult) |
| Authenticate a user | [Heddle](heddle.md) |
| Apply roles or abilities | [Heddle: authorization](heddle.md#roles-and-abilities) |
| Validate and store an upload | [Yarn](yarn.md) |
| Deliver a stored file | [Yarn: delivery](yarn.md#asset-delivery) |
| Record an auditable action | [Ledger](ledger.md) |
| Add an application controller | [Weave](weave.md) |
| Expose a JSON resource | [API](api.md) |
| Add or run a CLI command | [Shuttle](../shuttle.md) |
| Understand the complete request flow | [MVC](../mvc.md) |
| Deploy an instance | [Deployment](../deployment.md) |

## Namespace map

### Woodframe

```php
Loom73\Woodframe\Config
Loom73\Woodframe\Ctrl
Loom73\Woodframe\Template
Loom73\Woodframe\Flash
Loom73\Woodframe\Logger
Loom73\Woodframe\Mailman
Loom73\Woodframe\OwnerRegistry
Loom73\Woodframe\Debugger
Loom73\Woodframe\Errata
```

Woodframe provides the common structure used by controllers and other components.

### Beam

```php
Loom73\Beam\Connection
Loom73\Beam\Model
Loom73\Beam\QueryResult
```

Beam owns PDO connections, prepared statements, small CRUD primitives and normalized query outcomes.

### Heddle

```php
Loom73\Heddle\Auth
Loom73\Heddle\Session
```

Heddle resolves the current authenticated user and answers role or ability questions.

### Yarn

```php
Loom73\Yarn\Asset
Loom73\Yarn\AssetType
Loom73\Yarn\AssetValidator
Loom73\Yarn\AssetValidationResult
Loom73\Yarn\AssetStorage
Loom73\Yarn\AssetPolicy
Loom73\Yarn\AssetUploader
Loom73\Yarn\AssetUploadResult
Loom73\Yarn\AssetDelivery
```

Yarn manages uploaded files from validation through controlled delivery.

### Ledger

```php
Loom73\Ledger\Ledger
Loom73\Ledger\LedgerEvent
```

Ledger records meaningful user and system actions as an audit trail.

### Weave

```php
Loom73\Weave\Controllers
```

Weave is the application layer. Its controllers coordinate the reusable namespaces and select the final response.

### Shuttle

```php
Loom73\Shuttle
```

Shuttle provides explicit installation, inspection and maintenance commands.

## Shared conventions

### Explicit dependencies

Controllers instantiate the models and services they use:

```php
protected User $User;
protected Asset $Asset;

public function __construct(
    string $model,
    string $controller,
    string $method
) {
    parent::__construct($model, $controller, $method);

    $this->User = new User();
    $this->Asset = new Asset();
}
```

PascalCase properties identify reusable dependencies. CamelCase variables identify records, results and runtime values.

### Result objects

Operations that can fail during normal execution generally return an inspectable result:

```php
if ($result->fails()):
    // Handle the failure.
endif;

if ($result->isEmpty()):
    // The operation succeeded but returned no data.
endif;
```

Invalid method arguments, missing configuration and programming errors may still throw exceptions.

### Configuration

Environment variables describe one installed instance. PHP files under `config/modules/` organize component configuration.

```text
config/.env
    instance-specific secrets and values

config/modules/*.php
    structured component configuration
```

### Security boundaries

Loom73 keeps security decisions explicit:

```text
controllers
    apply route guards and validate requests

models
    bind database values

views
    escape output for its destination context

Yarn
    validates files and stores them outside the web root

Ledger
    records meaningful actions

Logger
    records operational failures
```

Frontend state, route names and database records are not access-control mechanisms by themselves.

## Reading path

A developer new to Loom73 should normally read:

1. [MVC](../mvc.md)
2. [Woodframe](woodframe.md)
3. [Beam](beam.md)
4. [Heddle](heddle.md)
5. [Yarn](yarn.md)
6. [Ledger](ledger.md)
7. [Weave](weave.md)
8. [API](api.md)
9. [Shuttle](../shuttle.md)
10. [Deployment](../deployment.md)

Applications can then return to this index and navigate by task.
