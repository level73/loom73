# Loom73

[![Deploy](https://img.shields.io/github/actions/workflow/status/level73/loom73/deploy-test.yml?branch=main&event=push&label=deploy)](https://github.com/level73/loom73/actions/workflows/deploy-test.yml)
[![Docs](https://img.shields.io/github/actions/workflow/status/level73/loom73/deploy-docs.yml?branch=main&label=docs)](https://github.com/level73/loom73/actions/workflows/deploy-docs.yml)
[![Release](https://img.shields.io/github/v/release/level73/loom73?display_name=tag)](https://github.com/level73/loom73/releases/latest)
![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?logo=php&logoColor=white)

**A clear foundation for building PHP applications.**

Loom73 is a lightweight, forkable application blueprint built from recurring architectural decisions, reusable components, and practical web-development experience.

It provides enough structure to avoid rebuilding the same foundations for every project, while remaining small enough to inspect, understand, and adapt.

> Not a framework to learn.  
> A foundation to build from.

```text
PHP 8.3+  ·  2 runtime PHP libraries  ·  0 JS frameworks  ·  0 CSS frameworks
```

## Why Loom73

Most web applications begin by rebuilding the same foundations:

- routing and request handling;
- configuration and environment loading;
- database access;
- authentication and authorization;
- sessions and user feedback;
- uploads and controlled file delivery;
- audit trails and operational logging;
- CLI installation and maintenance;
- frontend validation and interface utilities;
- deployment conventions;
- JSON delivery for selected resources.

Loom73 collects those recurring decisions in one explicit blueprint without forcing the application into a large framework or hiding its behavior behind unnecessary abstraction.

The guiding principle is:

> **Convention, not cage.**

## Principles

```text
Readable over clever.
Explicit over magical.
Small tools over large dependencies.
Conventions over repetition.
SQL when SQL is clearer.
Vanilla when Vanilla is enough.
Accountability without noise.
A foundation, not a cage.
```

Loom73 deliberately avoids a forced ORM, automatic CRUD generation, a mandatory service container, JavaScript frameworks, CSS frameworks, and automatic exposure of Models through the API.

## What is included

Loom73 currently provides:

- convention-based routing;
- a small, explicit MVC architecture;
- PDO-based persistence primitives and `QueryResult`;
- authentication, sessions, roles, abilities, and controller guards;
- asset validation, storage, ownership, visibility, replacement, and delivery;
- user-action auditing through Ledger;
- operational error logging with a PHP fallback;
- a lightweight, read-only JSON API layer;
- installation and maintenance commands through Shuttle;
- frontend form validation;
- sortable, searchable, and paginated tables;
- dialogs, dismissibles, in-view behaviors, and accessible tooltips;
- Stitch, a selectively usable SVG icon collection;
- build-and-sync deployment through GitHub Actions and `rsync`.

## Architecture

Loom73 is organized around a small set of conceptual modules.

| Namespace | Responsibility |
| --- | --- |
| `Loom73\Woodframe` | Configuration, base controller, templates, shared utilities, registries, and Logger |
| `Loom73\Beam` | PDO connection, base Model, explicit queries, and QueryResult |
| `Loom73\Heddle` | Authentication, sessions, roles, abilities, and authorization |
| `Loom73\Yarn` | Asset validation, storage, ownership, policy, and controlled delivery |
| `Loom73\Ledger` | Meaningful user-action auditing and retention |
| `Loom73\Weave` | Controllers, API delivery, and application orchestration |
| `Loom73\Shuttle` | Installation, maintenance, and project CLI commands |

The namespace may be metaphorical. Concrete class names remain direct and technical.

A typical request remains legible:

```text
URL
  → Controller
  → Method
  → Model or service when needed
  → View, redirect, JSON, or binary response
```

Routing follows a simple convention:

```text
first URL fragment   → controller
second URL fragment  → method
remaining fragments  → method parameters
```

For example:

```text
/user/profile
    → UserCtrl::profile()

/api/users/alan
    → ApiCtrl::users('alan')
```

Controllers instantiate only the dependencies they actually use. Beam reduces repetition, but SQL remains visible whenever SQL is the clearest tool.

## Technical foundation

### Runtime

- PHP 8.3 or newer;
- PHP 8.4 recommended;
- MySQL or MariaDB;
- PDO and PDO MySQL;
- Fileinfo;
- Composer;
- an Apache-compatible deployment is currently documented and supported.

The public web root must point to:

```text
public_html/public
```

Runtime data remains outside the public web root:

```text
config/.env
storage/uploads
storage/logs
storage/cache
database
```

### PHP dependencies

Loom73 currently relies on two focused runtime libraries:

- `vlucas/phpdotenv` for environment configuration;
- `phpmailer/phpmailer` for email delivery.

Database access uses native PDO. File inspection uses PHP's Fileinfo extension.

The objective is not dependency avoidance at all costs. A dependency should be added when it solves a defined problem better than maintaining an equivalent implementation inside Loom73.

### Frontend

Loom73 ships no JavaScript framework and no CSS framework.

The browser receives compiled CSS, Vanilla JavaScript, selected SVG assets, and application content. Node-based tooling is used only during development and deployment and is not shipped to the end user.

The frontend is organized around small exported modules:

```text
Loom73Forms
Loom73Tables
Loom73UI
```

The current build is driven by Grunt. A planned modernization milestone will replace it with focused tools while preserving the same framework-free runtime:

```text
Lightning CSS
esbuild
SVGO
Sharp
Node standard library
```

A lightweight theme layer is also planned to separate Loom73's functional UI primitives from the visual identity of each application.

## Quick start

Loom73 is intended to be forked or used as the starting point for a new application.

### 1. Install dependencies

```bash
composer install
npm ci
```

### 2. Build frontend assets

```bash
npm run build
```

For a deployment build:

```bash
npm run build:deploy
```

### 3. Configure the instance

Create the environment file from the provided example:

```bash
cp config/example.env config/.env
```

Then configure the database, application URL, mail delivery, and other instance-specific settings.

The real `.env` file must never be committed.

### 4. Install runtime state

Run Shuttle from the application root:

```bash
php shuttle loom73.install
```

This initializes the database and required runtime directories for a fresh instance.

> Review the command before running it against an existing or populated database.

### 5. Create an administrator

```bash
php shuttle user.admin
```

### 6. Configure the web server

Point the web server document root to:

```text
/path/to/application/public_html/public
```

The browser must not have direct access to the application, configuration, library, vendor, or storage directories.

## Shuttle

Current commands include:

```bash
php shuttle loom73.info
php shuttle loom73.install
php shuttle user.admin
php shuttle asset_type.new
php shuttle ledger.cleanup
```

Additional commands follow a simple file and class naming convention:

```text
commands/database.update.php
    → Loom73\Shuttle\DatabaseUpdate
    → php shuttle database.update
```

Shuttle is intentionally small. It is not intended to become a general console framework.

## Explicit assets, auditing, and API delivery

Yarn treats uploaded files as application resources rather than anonymous files in a public directory. Files are stored outside the web root and delivered through application-controlled routes.

Ledger records meaningful user actions rather than internal system noise. Operational failures belong to the Logger, and a Ledger failure never invalidates an otherwise completed user action.

The API layer is read-only by design in the current release. Resources are exposed deliberately through `ApiCtrl` and API-specific queries; Models and database columns are never serialized automatically.

```text
GET /api/users
GET /api/users/{username}
```

An endpoint may expose only `username` and `role` while keeping email addresses, password hashes, recovery tokens, and other internal fields private.

## Deployment model

Loom73 uses a build-and-sync deployment workflow:

```text
GitHub Actions
  → install Composer dependencies
  → install Node dependencies
  → build frontend assets
  → rsync application files to the VPS
```

The VPS owns instance-specific runtime state:

```text
config/.env
storage/
database
uploaded files
logs
cache
```

These elements are excluded from deployment and are never overwritten by a core update.

> Deploy code.  
> Install runtime state.  
> Preserve instance data.

## Repository structure

```text
application/        application controllers, models, and views
commands/           Shuttle commands
config/             configuration files and .env example
deploy/             deployment scripts and rsync exclusions
frontend-src/       frontend source files and build configuration
lib/                reusable Loom73 components
public_html/public/ public web root
storage/            runtime-only uploads, logs, and cache
vendor/             Composer dependencies
```

## Documentation

The documentation is being consolidated into a structured `documentation/` directory as part of the current public-foundation milestone.

It covers:

- requirements, installation, and configuration;
- architecture and conventions;
- individual Loom73 components;
- frontend utilities and theming;
- API delivery;
- deployment and runtime storage;
- forking, releases, and upgrades;
- optional integrations and extension patterns.

The README remains the entry point. Detailed implementation guidance belongs in the documentation rather than being duplicated here.

[Read the documentation](https://level73.github.io/loom73/)

## Project status

Loom73 6.0 establishes the first public foundation of the project.

The completed milestone focuses on documentation, the project landing page, repository readiness, and consolidation of the reference implementation.

Future additions are evaluated against three questions:

1. Is the problem recurring across real applications?
2. Is the capability useful without imposing an application-specific workflow?
3. Should it belong to the core, an optional package, or documentation only?

Capabilities such as data export, PDF generation, spreadsheet output, image derivatives, and API tokens are expected to remain optional until repeated use cases justify a shared abstraction.

## Built by Level73

Loom73 is developed by [Level73](https://lvl73.it) from patterns refined through the design, development, deployment, and maintenance of real web applications.
