# Namespacing Loom73

## Base namespace

The base namespace is:

```php
Loom73\
```

Loom73 namespaces are organized around the loom metaphor. They are not intended to mirror the filesystem mechanically. They describe the conceptual responsibility of each component.

The namespace tells us **what kind of work a class performs**, not simply where the file happens to live.

Loom73 uses namespacing to preserve clarity, not to create unnecessary abstraction. The metaphor belongs primarily to the architectural modules; class names should remain direct, technical and readable.

Good:

```php
Loom73\Yarn\AssetUploader
Loom73\Beam\QueryResult
Loom73\Woodframe\OwnerRegistry
```

Less good:

```php
Loom73\Yarn\ThreadSpinner
Loom73\Beam\LightCaster
```

The namespace may be poetic. The class should say what it does.

---

## Namespace principles

Loom73 follows a small set of namespace principles:

```text
Namespaces describe responsibility.
Namespaces should not hide application logic.
Core modules should remain reusable.
Application code should remain visibly application-specific.
The framework should support conventions, not impose a cage.
```

A namespace is not a service container, not an ORM boundary, and not a reason to over-abstract code that is better left explicit.

The guiding rule is:

```text
Use namespaces to make the architecture legible.
Do not use namespaces to make simple things look sophisticated.
```

---

## Autoloading

Loom73 supports namespaced classes under the base namespace:

```php
Loom73\
```

The autoloader resolves namespaced classes primarily from the reusable library area, while still allowing legacy or application-specific loading where needed.

The intended direction is:

```text
Loom73\Woodframe\*
Loom73\Beam\*
Loom73\Heddle\*
Loom73\Yarn\*
Loom73\Shuttle\*
Loom73\Ledger\*
```

mapped from the reusable core/library structure.

Application-specific code may live under the application layer while still using the `Loom73\Weave\` namespace when appropriate.

---

# Core namespaces

## `Loom73\Woodframe\`

`Woodframe` is the structural frame of Loom73.

It contains the stable foundation shared by the rest of the system: configuration, base controllers, templates, debug helpers, generic utilities and registries that describe application-wide conventions.

Typical responsibilities:

```text
configuration access
base controller behavior
template/rendering support
debugging helpers
shared framework utilities
application-level registries
```

Examples:

```php
Loom73\Woodframe\Config
Loom73\Woodframe\Ctrl
Loom73\Woodframe\Template
Loom73\Woodframe\OwnerRegistry
```

### OwnerRegistry

`OwnerRegistry` belongs to `Woodframe`, not to `Yarn`.

This is an important architectural decision.

Owners are logical application entities:

```text
user
project
partner
event
case
document
```

They may be used by assets, audit logs, metadata, lexicons, relations or future modules. For this reason, ownership is not an asset-specific concept.

`Yarn` may consult `OwnerRegistry`, but `OwnerRegistry` must not depend on `Yarn`.

Dependency direction:

```text
Woodframe\OwnerRegistry
  used by Yarn
  used by Ledger
  used by future metadata systems
```

not:

```text
Yarn owns the concept of owners
```

The owner registry defines logical application ownership. It does not replace database relations, and it does not make every owner type a database table.

---

## `Loom73\Beam\`

`Beam` is the persistence and data-access layer.

It provides database connection handling, query results and a small base model layer. It is intentionally lightweight.

Beam is not an ORM.

Beam is not a query-builder-first abstraction.

Beam exists to offer a stable minimum layer over PDO while keeping SQL visible where SQL is the clearest tool.

Typical responsibilities:

```text
PDO connection management
base model helpers
query execution
query result wrapping
small CRUD primitives
explicit SQL support
```

Examples:

```php
Loom73\Beam\Connection
Loom73\Beam\Model
Loom73\Beam\QueryResult
```

The base model may provide ordinary helpers such as:

```text
getById
getBy
create
updateById
updateWhere
deleteById
softDeleteById
```

But concrete models should still write explicit SQL for:

```text
joins
reports
aggregations
domain-specific reads
performance-sensitive queries
complex filters
```

The design principle is:

```text
Beam reduces repetition.
It does not hide the database.
```

### QueryResult

`QueryResult` represents the outcome of a persistence operation.

It allows the application to distinguish between:

```text
successful query with data
successful query with no data
failed query
insert result
update result
database exception
```

This keeps controllers and services from relying directly on raw PDO return values or scattered exception handling.

---

## `Loom73\Heddle\`

`Heddle` handles authentication, sessions, authorization and access control.

It contains the machinery that decides who is present, what role they have, and what they are allowed to do.

Typical responsibilities:

```text
login
logout
session validation
authenticated profile retrieval
role checks
ability checks
authorization guards
```

Examples:

```php
Loom73\Heddle\Session
Loom73\Heddle\Auth
```

`Auth` may expose methods such as:

```php
isLoggedIn()
getProfile()
hasRole()
can()
```

Authorization should remain explicit at the controller or use-case level.

For example:

```php
$this->requireAuth();
$this->requireAbility('manage_users');
```

Public routes remain public unless a controller method explicitly requires authentication or a specific ability.

The presence of `Auth` in the base controller does not make the whole application private. It only makes the authentication context available.

---

## `Loom73\Yarn\`

`Yarn` manages uploaded assets and file resources.

It handles validation, storage, asset metadata, owner-slot policies, upload orchestration and controlled delivery of files stored outside the public web root.

Typical responsibilities:

```text
upload validation
MIME/type validation
storage outside webroot
asset metadata registration
asset type resolution
owner slot policy checks
single-slot replacement
file delivery
download/inline response handling
```

Examples:

```php
Loom73\Yarn\Asset
Loom73\Yarn\AssetType
Loom73\Yarn\AssetValidator
Loom73\Yarn\AssetStorage
Loom73\Yarn\AssetPolicy
Loom73\Yarn\AssetUploader
Loom73\Yarn\AssetDelivery
```

Yarn distinguishes between:

```text
owner_type
owner_id
owner_slot
asset_type
```

Example:

```text
owner_type = user
owner_id = 1
owner_slot = avatar
asset_type = image_avatar
```

Where:

```text
owner_type
  logical application owner, validated through OwnerRegistry

owner_id
  identifier of the owned object

owner_slot
  contextual placement of the file in relation to the owner

asset_type
  semantic file type, stored as a DB-backed asset type
```

`owner_type` is not a database foreign key. It is a logical application key.

`asset_type` is a database-backed semantic classification.

### Asset controllers

HTTP controllers for assets do not belong inside `Yarn`.

For example:

```php
AssetCtrl
```

belongs to the application/controller layer, because it is a route-facing HTTP bridge.

The separation is:

```text
Yarn\AssetDelivery
  knows how to deliver a file

Weave\Controllers\AssetCtrl
  knows how to respond to an HTTP request
```

This keeps `Yarn` reusable and free from application routing assumptions.

---

## `Loom73\Weave\`

`Weave` is the application orchestration layer.

It contains the code that connects incoming requests, application decisions, models, services, views and responses.

Typical responsibilities:

```text
application controllers
request orchestration
use-case coordination
application-specific services
API handlers
view data preparation
redirect and response flow
```

Examples:

```php
Loom73\Weave\Controllers\UserCtrl
Loom73\Weave\Controllers\AssetCtrl
```

`Weave` is where the application is allowed to be specific.

It is the place where reusable Loom73 primitives are combined into actual behavior:

```text
Auth context from Heddle
data access from Beam
asset handling from Yarn
configuration from Woodframe
audit logging from Ledger
```

A controller in `Weave` should not become a dumping ground, but it is allowed to express the actual flow of the application clearly.

The rule is:

```text
Reusable mechanisms live in core namespaces.
Application decisions live in Weave.
```

---

## `Loom73\Shuttle\`

`Shuttle` provides command-line tooling and project automation.

It is responsible for tasks that are better performed from the CLI than from the web application.

Typical responsibilities:

```text
installation commands
database setup
seed data
admin user creation
runtime directory initialization
maintenance commands
cleanup routines
developer utilities
```

Examples:

```php
Loom73\Shuttle\Installer
Loom73\Shuttle\Command
```

`Shuttle` is not only a database installer.

The install command represents the initialization of a Loom73 instance. It may therefore create:

```text
database tables
seed data
runtime directories
initial users
required registry values
```

For example, `storage/` is runtime state and should be created by Shuttle during installation, not deployed from GitHub.

The principle is:

```text
Deploy code.
Install runtime state.
```

---

## `Loom73\Ledger\`

`Ledger` is the audit and activity logging namespace.

It is intended for recording relevant operations performed inside the application.

Typical responsibilities:

```text
audit events
activity logs
actor/action/owner tracking
change summaries
security-relevant records
administrative traceability
```

Expected examples:

```php
Loom73\Ledger\Ledger
Loom73\Ledger\LedgerEvent
```

Ledger should be able to record events such as:

```text
auth.login
auth.logout
user.create
user.update
asset.upload
asset.deactivate
asset.download
```

Ledger may use `OwnerRegistry` to validate logical owners, but `OwnerRegistry` must not depend on Ledger.

Dependency direction:

```text
Ledger uses Woodframe\OwnerRegistry
```

not:

```text
OwnerRegistry knows about Ledger
```

Ledger is especially important for CRM, backoffice and light CMS use cases, where administrative actions need to remain traceable.

---

# Application-specific code

Loom73 separates the reusable blueprint from the application built on top of it.

A typical project may contain:

```text
/application
  controllers/
  models/
  views/
```

This area is project-specific.

It may use the `Loom73\Weave\` namespace for controllers and orchestration classes, but it should remain conceptually distinct from the reusable core modules.

Application-specific models may extend `Loom73\Beam\Model`, but their domain logic belongs to the application, not to Beam itself.

---

# Namespace boundaries

## Woodframe vs Yarn

`OwnerRegistry` belongs to `Woodframe`.

`AssetPolicy` belongs to `Yarn`.

Why?

```text
OwnerRegistry defines logical application owners.
AssetPolicy defines how assets may attach to those owners.
```

The owner concept is wider than assets.

---

## Yarn vs Weave

`AssetUploader` belongs to `Yarn`.

`AssetCtrl` belongs to `Weave`.

Why?

```text
AssetUploader performs upload orchestration.
AssetCtrl handles HTTP routing, guards and responses.
```

Yarn should remain usable without assuming a specific routing layer.

---

## Beam vs application models

`Beam\Model` belongs to `Beam`.

Concrete domain models belong to the application.

Why?

```text
Beam provides persistence primitives.
Application models express domain-specific data access.
```

Beam should not become a universal ORM.

---

## Heddle vs controller guards

`Auth` belongs to `Heddle`.

`requireAuth()` and `requireAbility()` may live in the base controller.

Why?

```text
Heddle knows authentication and authorization.
Controllers decide which routes require which abilities.
```

This keeps access control explicit.

---

# Naming discipline

Loom73 uses evocative namespace names, but class names should remain practical.

The metaphor should help orient the architecture. It should not obscure behavior.

Recommended style:

```php
Loom73\Yarn\AssetUploader
Loom73\Yarn\AssetDelivery
Loom73\Beam\QueryResult
Loom73\Woodframe\Config
Loom73\Heddle\Auth
```

Avoid names that require interpretation before reading the code.

The goal is that a developer returning to the project after two years can still understand what each class does without remembering an internal mythology.

---

# Summary

```text
Loom73\Woodframe
  framework structure, configuration, base utilities, registries

Loom73\Beam
  database connection, base model, query result, persistence primitives

Loom73\Heddle
  authentication, sessions, roles, abilities, access control

Loom73\Yarn
  asset validation, upload, storage, delivery, asset policies

Loom73\Weave
  application orchestration, controllers, use cases, route-facing logic

Loom73\Shuttle
  CLI tooling, installation, maintenance, automation

Loom73\Ledger
  audit events, activity logging, traceability
```

The overall rule:

```text
Woodframe holds the frame.
Beam carries persistence.
Heddle controls access.
Yarn manages resources.
Weave composes the application.
Shuttle installs and maintains the instance.
Ledger records what happened.
```
