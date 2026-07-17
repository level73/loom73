
# Namespacing Loom73

## Base namespace

The base namespace is `Loom73\`.

Loom73 namespaces are organized around the loom metaphor. They are not intended to mirror the application directory structure mechanically, but to describe the conceptual responsibility of each component.

## Secondary namespaces

### `Loom73\Woodframe\`

Core framework structure and shared infrastructure.

Includes base controllers, templating, configuration, mailing wrappers, error handling, debugging utilities and other foundation classes.

### `Loom73\Heddle\`

Authentication, authorization and access control.

Includes login/session handling, RBAC primitives, permissions, roles, gates and policies.

### `Loom73\Yarn\`

Asset and resource management.

Includes uploads, storage, metadata, MIME validation, file delivery, generated derivatives and asset-related policies.

### `Loom73\Beam\`

Persistence and data access.

Includes database connections, query utilities, base models and persistence-related abstractions. This namespace is optional in applications that do not require a database.

### `Loom73\Weave\`

Application orchestration.

Includes controllers, services, API handlers, use-case classes and other application-specific logic that weaves requests, data and responses together.

### `Loom73\Shuttle\`

Command-line tooling.

Includes CLI commands, console utilities, maintenance tasks and project automation helpers.
