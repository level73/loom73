# Loom73 Roadmap

The roadmap describes planned work. Current behavior belongs in the component documentation and completed release work belongs in the changelog.

Loom73 evaluates additions against three questions:

1. Does the problem recur across real applications?
2. Is the capability useful without imposing an application-specific workflow?
3. Does it belong in the core, an optional integration, or documentation?

## 6.0 — Public Foundation

**Status: complete**

Loom73 6.0 establishes the first public foundation of the project.

### Completed foundation

```text
PHP 8.3 minimum baseline
public landing and architecture pages
component demonstrations
responsive and accessible frontend baseline
tooltip and responsive navigation
404 and 500 responses
deployment workflow
runtime health inspection
repository pre-publication review
complete and reorganize repository documentation
document every current backend component
document frontend utilities and Stitch
align README and documentation links
finalize repository metadata and license presentation
prepare the first public 6.0 release
```

Version 6.0 should not gain another large subsystem.

## 6.1 — Frontend Modernization

Replace the current Grunt pipeline with a focused build script while preserving the framework-free browser runtime.

Target toolset:

```text
build.mjs
Lightning CSS
esbuild
SVGO
Sharp
node:fs
```

### First phase: build parity

```text
CSS concatenation and minification
JavaScript bundling and minification
SVG optimization
WebP conversion
font and static-asset copying
development watch mode
deployment build
```

Grunt and its plugins should be removed only after the new build produces equivalent output.

### Second phase: build improvements

```text
explicit CSS entry point
explicit ES module graph
development source maps
declared browser targets
fail-fast builds
build summary
esbuild metafile
optional bundle-size guardrails
```

### Theme layer

The same milestone introduces the first theme boundary:

```text
Loom73 core
    behavior and functional UI contracts

plain theme
    colors, typography and component appearance

application
    domain-specific visual decisions
```

Planned configuration:

```env
LOOM73_THEME='plain'
```

The first implementation should provide one theme identifier resolved by convention.

It should not introduce:

```text
theme inheritance
child themes
template overrides
database theme selection
plugin lifecycle
```

## 6.2 — Gauge

Gauge is a small, opt-in development profiler for a single request.

Initial measurements:

```text
response time
current memory
peak memory
database query count
database query time
PHP version
HTTP status
route or controller
```

Gauge should:

```text
remain disabled by default
run only in an allowed development environment
store no measurements in the database
send no remote telemetry
add minimal overhead
render a small developer-facing status bar
```

Beam can collect query count and timing at the point where it executes statements. Gauge reads the aggregate values at the end of the request.

Possible Shuttle operations:

```text
php shuttle gauge.info
php shuttle gauge.enable
php shuttle gauge.disable
```

The exact enablement mechanism must be defined before implementation. A runtime flag and browser opt-in may allow a shared development instance to expose Gauge only to the developer using it.

Gauge measures the current request. Logger records operational failures. Ledger records meaningful user actions.

```text
Gauge   what this request cost
Logger  what failed operationally
Ledger  what an actor did
```

## 6.3 — Release and Upgrade Path

Formalize:

```text
Semantic Versioning
Git tags
CHANGELOG discipline
release notes
compatibility notes
```

Introduce a safe distinction between:

```text
loom73.install
    create a fresh runtime and schema

loom73.update
    apply missing changes to an existing instance
```

The first update mechanism should remain small:

```text
record installed schema or blueprint version
discover ordered update scripts
run missing updates sequentially
record successful completion
stop and report failures
```

A full migration framework is not required.

## 6.4 — Automated Confidence

Add focused tests around stable contracts.

Priority areas:

```text
autoloading
routing and error responses
Config
Connection and QueryResult
Model CRUD and identifier validation
authentication and guards
Yarn validation, replacement and delivery
Ledger recording and retention
API responses and method restrictions
Shuttle command resolution
```

Add an installation smoke test:

```text
empty instance
  → loom73.install
  → expected schema and seed data
  → runtime directories
  → loom73.info
```

The target CI matrix is:

```text
PHP 8.3
PHP 8.4
```

with:

```text
Composer validation
platform requirement checks
PHP lint
focused tests
npm clean install
frontend build
deployment validation
```

## Later — Operational Interfaces

These are interfaces over capabilities that already exist.

### Ledger viewer

```text
event date
actor
action
owner
summary
metadata
search and filtering
```

### Yarn asset manager

```text
asset list
preview
owner and type filters
download
visibility and status
deactivation
storage metadata
```

They should remain application-facing examples or optional interfaces unless repeated projects establish a stable shared contract.

## Extension track

### Data export

Recognize exports as a recurring capability without adding heavy core dependencies.

```text
CSV
    native PHP streaming with fputcsv()

XLSX
    OpenSpout for large, simple tabular exports
    PhpSpreadsheet for complex workbooks

PDF
    evaluate a renderer according to the document use case
```

Possible shared abstractions should be extracted only after use in real applications.

### API authentication

The API remains read-only and uses application/session protection where required.

When a machine-to-machine use case exists, Heddle may gain:

```text
token generation
hashed token storage
expiration
revocation
abilities or scopes
last-used metadata
```

API mutation is not implied by token authentication.

### Yarn image derivatives

A future optional integration may provide:

```text
crop
resize
avatar variants
thumbnails
responsive derivatives
GD or Imagick adapters
```

Image processing should remain optional until a stable cross-project contract emerges.

## Deliberately not planned

Loom73 does not currently plan to add:

```text
generic caching abstraction
full REST framework
ORM
general query builder
mandatory service container
JavaScript framework
CSS framework
plugin framework
theme inheritance
automatic Model exposure through the API
```

The installed `storage/cache/` directory remains available as runtime infrastructure for applications that need local caching. Its presence does not imply a Loom73 caching API.

For ordinary deployments, begin with:

```text
OPcache
clear SQL
appropriate indexes
sensible queries
HTTP cache headers
```

Introduce application caching only when the expensive operation and its invalidation strategy are understood.
