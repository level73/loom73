# Namespacing Loom73

The base namespace is:

```php
Loom73\
```

Loom73 uses names inspired by the parts of a loom to describe broad architectural responsibilities. The metaphor provides orientation; concrete class names remain direct and technical.

```text
Namespaces carry the metaphor.
Classes carry the responsibility.
```

Good:

```php
Loom73\Beam\QueryResult
Loom73\Yarn\AssetUploader
Loom73\Woodframe\OwnerRegistry
```

Avoid names whose meaning depends on understanding an internal metaphor:

```php
Loom73\Yarn\ThreadSpinner
Loom73\Beam\LightCaster
```

## Principles

Loom73 namespaces follow a few rules:

```text
Namespaces describe responsibility.
Reusable mechanisms stay separate from application decisions.
Application logic remains visible.
Simple behavior should not be abstracted merely to fit a namespace.
```

The namespace structure does not introduce a service container, plugin system or mandatory dependency-injection layer.

## Autoloading

Loom73 currently uses its own autoloader.

A namespaced class such as:

```php
Loom73\Yarn\AssetUploader
```

is first resolved to:

```text
lib/Yarn/AssetUploader.php
```

If no matching library file exists, Loom73 searches the directories configured in `LOOM73['directories']`, including:

```text
lib/
commands/
application/models/
application/controllers/
```

The fallback uses the class basename. Class names should therefore remain unique across these directories.

Application code does not have to live under `lib/`. A class may be physically application-specific while still using the namespace that best describes its responsibility.

For example:

```text
application/controllers/UserCtrl.php
    → Loom73\Weave\Controllers\UserCtrl

application/models/User.php
    → Loom73\Beam\User
```

The first coordinates an HTTP request. The second performs persistence work for the application.

## `Loom73\Woodframe\`

Woodframe contains the structural foundation shared by the rest of Loom73.

Current classes include:

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

Its responsibilities include:

```text
configuration
base controller behavior
template composition
flash messages
mail delivery
operational logging
shared registries
debugging and error support
```

Woodframe should contain mechanisms that are useful across multiple parts of the application. Domain-specific decisions belong elsewhere.

### Configuration

Module configuration files live in:

```text
config/modules/
```

Each file returns an array. `Config` loads these files and exposes values through dot notation:

```php
Config::get('database.default');
Config::get('yarn.storage_path');
Config::get('ledger.enabled', false);
```

### OwnerRegistry

`OwnerRegistry` defines the logical entities that other components may reference:

```text
user
project
partner
event
document
```

Owners are wider than asset ownership. Yarn and Ledger may both consult the registry, so the registry belongs to Woodframe.

The dependency direction is:

```text
Woodframe\OwnerRegistry
    ↓
Yarn, Ledger and application code
```

Woodframe must not depend on Yarn or Ledger to understand what an owner is.

## `Loom73\Beam\`

Beam is the persistence and data-access layer.

Current core classes include:

```php
Loom73\Beam\Connection
Loom73\Beam\Model
Loom73\Beam\QueryResult
```

Concrete application models may also use the Beam namespace when their primary responsibility is data access.

Beam provides:

```text
shared PDO connection handling
prepared statements
normalized query results
small CRUD primitives
identifier validation
soft-delete helpers
explicit SQL support
```

Beam is not an ORM and does not attempt to replace SQL with a universal query builder.

```text
Beam reduces repetition.
It does not hide the database.
```

### Connection

`Connection::pdo()` returns the shared PDO instance, creating it on first use.

`Connection::make()` creates an independent PDO connection from the configured database connection or from an explicitly supplied configuration array.

`Connection::set()` and `Connection::reset()` allow the shared connection to be replaced or cleared.

`Connection::ping()` prepares and executes `SELECT 1`. It is intended for operational health checks such as:

```bash
php shuttle loom73.info
```

A connection health check returns a boolean. It is not a domain query and does not require a `QueryResult`.

### Model

`Model` provides common operations such as:

```text
getById
getBy
all
create
updateById
updateWhere
updateRawWhere
deleteById
softDeleteById
restoreById
getFields
```

Concrete models should write explicit SQL when a query expresses:

```text
joins
unions
subqueries
aggregations
reports
domain-specific reads
performance-sensitive behavior
```

Values must be bound as parameters. Dynamic identifiers must pass through the identifier validation provided by Model.

### QueryResult

Database operations return a `QueryResult` containing:

```text
success
rows
data
insertId
error
```

Typical usage:

```php
$result = $this->User->getById($id);

if ($result->fails()):
    // Handle the database failure.
endif;

if ($result->isEmpty()):
    // The query succeeded but found no record.
endif;

$user = $result->first();
```

`QueryResult` represents query execution outcomes. Invalid API usage and programming errors may still throw exceptions.

## `Loom73\Heddle\`

Heddle handles authentication, sessions and authorization.

Current classes include:

```php
Loom73\Heddle\Auth
Loom73\Heddle\Session
```

Its responsibilities include:

```text
login and logout
session validation
authenticated profile retrieval
role checks
ability checks
```

The base controller makes the authentication context available, but routes remain public until a controller explicitly applies a guard:

```php
$this->requireAuth();
$this->requireAdmin();
$this->requireEditor();
$this->requireAbility('manage_users');
```

Heddle determines authentication and authorization state. Controllers decide which application actions require that state.

## `Loom73\Yarn\`

Yarn manages uploaded files as application resources.

Current classes include:

```php
Loom73\Yarn\Asset
Loom73\Yarn\AssetType
Loom73\Yarn\AssetValidator
Loom73\Yarn\AssetStorage
Loom73\Yarn\AssetPolicy
Loom73\Yarn\AssetUploader
Loom73\Yarn\AssetDelivery
Loom73\Yarn\AssetValidationResult
Loom73\Yarn\AssetUploadResult
```

Yarn owns:

```text
upload validation
MIME and extension validation
storage outside the public web root
asset metadata
owner-slot policy
single-slot replacement
visibility
inline and attachment delivery
```

Yarn distinguishes between:

```text
owner_type
    logical kind of owning entity

owner_id
    identifier of that entity

owner_slot
    contextual position of the asset

asset_type
    semantic file classification stored in the database
```

Example:

```text
owner_type = user
owner_id = 12
owner_slot = avatar
asset_type = image_avatar
```

HTTP routing does not belong to Yarn. For example:

```text
Yarn\AssetDelivery
    validates and emits a file response

Weave\Controllers\AssetCtrl
    handles the route, authentication and response choice
```

## `Loom73\Ledger\`

Ledger records meaningful application actions.

Current classes include:

```php
Loom73\Ledger\Ledger
Loom73\Ledger\LedgerEvent
```

An event may record:

```text
actor
action
owner type and identifier
summary
metadata
IP address
user agent
creation time
```

Examples of suitable actions:

```text
auth.login
auth.logout
user.create
user.update
asset.upload
asset.deactivate
asset.download
```

Ledger is an audit trail. Operational failures belong to `Woodframe\Logger`.

A Ledger failure must not invalidate an application action that has otherwise completed successfully.

## `Loom73\Weave\`

Weave is the application orchestration layer.

Controllers currently use:

```php
Loom73\Weave\Controllers
```

Weave combines reusable Loom73 components into project-specific behavior:

```text
request handling
controller actions
authorization decisions
model and service coordination
view preparation
redirects
JSON responses
binary responses
```

Reusable mechanisms belong to their core namespaces. Decisions about what the application does belong to Weave.

## `Loom73\Shuttle\`

Shuttle contains command-line tooling.

The reusable CLI helper lives at:

```php
Loom73\Shuttle\CLI
```

Command classes live in:

```text
commands/
```

and use the same namespace:

```php
Loom73\Shuttle
```

Shuttle handles tasks such as:

```text
instance installation
health inspection
administrator creation
asset-type creation
Ledger cleanup
maintenance and diagnostics
```

Shuttle should remain a small command resolver and a collection of explicit commands rather than becoming a general console framework.

## Dependency direction

The intended dependency flow is:

```text
Woodframe
    shared structure and registries

Beam
    persistence primitives

Heddle
    authentication and authorization

Yarn
    asset lifecycle

Ledger
    auditing

Weave
    application orchestration

Shuttle
    command-line operations across the instance
```

More concretely:

```text
Weave may use Woodframe, Beam, Heddle, Yarn and Ledger.
Yarn may use Beam and Woodframe.
Ledger may use Beam and Woodframe.
Heddle may use Beam.
Core components must not depend on application controllers.
```

## Naming discipline

The metaphor should make the architecture memorable without making the code cryptic.

Use direct class names:

```php
AssetUploader
AssetDelivery
QueryResult
OwnerRegistry
Logger
Auth
```

In controllers, Loom73 distinguishes dependencies from runtime data:

```php
$this->User;       // model or service
$this->Asset;      // model or service
$this->Auth;       // reusable component

$user;             // record or runtime value
$asset;            // record or runtime value
$result;           // operation result
```

This convention is intentional and should be applied consistently.

## Summary

```text
Woodframe  structural foundation
Beam       persistence
Heddle     authentication and access
Yarn       uploaded resources
Ledger     audit trail
Weave      application orchestration
Shuttle    command-line operations
```

The governing rule remains:

```text
Use namespaces to make responsibility legible.
Do not use them to make simple code look sophisticated.
```

---