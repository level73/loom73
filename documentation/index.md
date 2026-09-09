# Loom73 Documentation

Loom73 is a small, forkable PHP application blueprint built around explicit architecture, focused components and conventions refined through real applications.

This documentation explains how Loom73 works, how to configure and operate an instance, and how to adapt the blueprint without obscuring the resulting application.

If you are discovering Loom73 for the first time, begin with the [project README](../README.md). It provides the project overview, principles, technical requirements and initial setup procedure.

## How this documentation is organized

The documentation follows the path of a developer adopting Loom73:

```text
Getting started
    install and configure an instance

Architecture
    understand request flow and responsibility boundaries

Components
    learn the contracts provided by Loom73

Frontend
    use the browser-side utilities and UI conventions

API
    expose selected application resources as JSON

Operations
    deploy, inspect and maintain an instance

Extending
    add application-specific behavior and optional integrations

Development
    work on Loom73 itself
```

Loom73 is intended to be read as well as used. The documentation therefore explains both how a feature works and why its responsibility belongs where it does.

## Start here

Use these documents for the current installation path:

- [Project README](../README.md) — overview, principles, requirements and quick start.
- [Deployment guide](deployment.md) — server structure, GitHub Actions, runtime state and deployment procedure.
- [Shuttle](shuttle.md) — installation, inspection and maintenance from the command line.
- [Roadmap](roadmap.md) — current milestone, planned work and deliberately excluded features.

Dedicated guides for requirements, configuration and the first application run will be extracted from the README and deployment guide as the documentation is consolidated.

## Architecture

Loom73 uses a small and explicit request flow:

```text
URL
  → Controller
  → Method
  → Model or service when needed
  → View, redirect, JSON or binary response
```

The following documents describe that structure:

- [MVC and request flow](mvc.md) — routing, controllers, models, views, request methods and response paths.
- [Namespaces and responsibility boundaries](namespaces.md) — Woodframe, Beam, Heddle, Yarn, Ledger, Weave and Shuttle.
- [Labels and user feedback](labels.md) — naming conventions for interface labels and application messages.

The namespace may carry the loom metaphor. Concrete class names remain direct and technical:

> Namespaces carry the metaphor. Classes carry the responsibility.

Future architecture documents will cover configuration, the complete request lifecycle, application conventions and dependency boundaries in greater detail.

## Components

Loom73 components solve recurring application problems while keeping their behavior visible and adaptable.

### Woodframe

The structural foundation of the application: configuration, controllers, templates, shared utilities, registries and operational logging.

A dedicated Woodframe reference is in preparation.

### Beam

PDO connection handling, the base `Model`, explicit SQL execution and normalized `QueryResult` objects.

Beam reduces repetition without hiding SQL. Common CRUD operations are available as conveniences; domain-specific queries remain explicit inside concrete models.

A dedicated Beam reference is in preparation.

### Heddle

Authentication, sessions, roles, abilities and authorization.

A dedicated Heddle reference is in preparation.

### Yarn

Validation, storage, ownership, visibility, replacement and controlled delivery of uploaded assets.

Yarn stores runtime files outside the public web root and leaves access decisions to explicit application policies.

A dedicated Yarn reference is in preparation.

### Ledger

Auditing of meaningful user actions, with configurable retention.

Ledger records what a user did. Operational failures and diagnostic information belong to the Logger.

A dedicated Ledger reference is in preparation.

### Shuttle

Installation and maintenance commands for a Loom73 instance.

See the current [Shuttle documentation](shuttle.md).

### Logger

Application error logging with a PHP logging fallback when Loom73 cannot write its own log.

A dedicated Logger reference is in preparation.

### Gauge

Gauge is a planned development utility for inspecting request time, memory use and database activity. It belongs to the roadmap and should not be treated as an available component until its runtime contract has been implemented.

## Frontend

Loom73 ships without a JavaScript framework or CSS framework. Browser behavior is provided by small Vanilla JavaScript modules and semantic HTML contracts.

See the current [frontend documentation](frontend.md).
 - [Forms](frontend/forms.md)
 - [Tables](frontend/tables.md)
 - [UI Utilities](frontend/ui.md)
 - [Stitch Icon Library](frontend/stitch.md)

The consolidated frontend documentation will cover:

- form validation and accessible feedback;
- sortable, searchable and paginated tables;
- dialogs and dismissible elements;
- accessible tooltips;
- responsive navigation;
- in-view behaviors and progressive enhancement;
- Stitch icons;
- CSS layers and utility conventions;
- the planned separation between core behavior, theme and application styles.

The public Components pages provide live examples. These Markdown documents provide the complete markup, options, accessibility requirements and implementation notes.

## API

The current API layer is deliberately small and read-only.

Resources are exposed explicitly through application controllers and API-specific queries. Models and database columns are never serialized automatically.

The API documentation will cover:

- exposing a resource;
- response and error formats;
- HTTP status handling;
- public and session-protected endpoints;
- field selection and sensitive-data boundaries;
- the current limits of the read-only API.

Token authentication and write operations are outside the current release contract.

## Operations

Operational documentation explains the boundary between deployed code and instance-owned runtime state.

Current references:

- [Deployment guide](deployment.md)
- [Shuttle commands](shuttle.md)

This area will eventually contain separate references for:

- runtime directories;
- environment configuration;
- Logger output;
- Ledger retention;
- installation and health checks;
- GitHub Actions and VPS deployment;
- backups and instance-owned data.

The governing deployment principle is:

```text
Deploy code.
Install runtime state.
Preserve instance data.
```

## Extending Loom73

Loom73 is a blueprint rather than a package that owns the resulting application.

Extension guides will explain how to:

- add application controllers and models;
- write explicit domain queries with Beam;
- define owners and asset types;
- add Shuttle commands;
- expose API resources;
- fork Loom73 for a new project;
- add optional dependencies without expanding the core;
- integrate exports, image processing and other project-specific capabilities.

Features should enter the shared blueprint only when they solve a recurring problem without imposing a project-specific workflow.

## Developing Loom73

Development documentation will cover:

- coding and naming conventions;
- PHP alternative control syntax in templates;
- frontend source organization;
- the asset build;
- supported PHP and browser baselines;
- testing and installation smoke checks;
- versioning, releases and upgrades;
- contribution guidelines.

Planned behavior belongs in the [roadmap](roadmap.md). Reference documentation should describe behavior that exists in the current codebase.

## Documentation principles

Documentation should remain close to the code and travel with every fork.

Each technical document should answer, where applicable:

1. What responsibility does this component own?
2. When should an application use it?
3. What is its smallest working example?
4. Which configuration and public methods form its contract?
5. What security, accessibility or runtime constraints apply?
6. Where does the component stop and application-specific code begin?

Examples should use the real Loom73 API and conventions. The public website may demonstrate a feature, while these documents remain the source for implementing and maintaining it.