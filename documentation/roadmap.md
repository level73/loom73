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

**Status: complete**

Modernize the frontend architecture and toolchain while preserving the framework-free browser runtime.

### Build foundation

**Status: complete**

The Node.js build runner provides:

```text
CSS import bundling and minification
automatic theme entry discovery and compilation
JavaScript module bundling and minification
package-version injection
SVG optimization
configurable WebP conversion
font and static-asset copying
aggregate build and optimization tasks
development watch mode
development-only source maps
shared browser targets for CSS and JavaScript
esbuild metadata used for build metrics
source-aware error reporting
colored success and failure output
per-task and aggregate timing
```

The legacy Grunt pipeline and its plugins were removed after validating:

```text
npm clean installation
one-off frontend build
deployment build
development watch mode
new and modified asset handling
generated public asset paths
frontend behavior
```

### Frontend source contract

**Status: complete**

The frontend now uses explicit source entry points:

```text
frontend-src/css/index.css
frontend-src/js/index.js
```

CSS dependencies are expressed through imports rather than a source list owned by the build runner.

JavaScript modules import their source files directly and are bundled into a single browser entry point.

Production builds generate deployable assets without source maps. Development watch mode generates linked source maps for CSS and JavaScript.

Browser targets are configured once in `frontend-src/build.mjs` and passed to both Lightning CSS and esbuild.

### Layout contract

**Status: complete**

The core layout layer provides reusable page sections, bounded content, breakout sections and adaptable grid patterns.

The contract favors a small set of configurable layout primitives over a large collection of fixed grid combinations. Application-specific spacing, borders and presentation remain outside the reusable layout layer.

### Theme boundary

**Status: complete**

The frontend is divided into:

```text
Loom73 core
    reusable structure, behavior and functional UI contracts

themes
    colors, typography and component presentation

application
    domain-specific visual decisions
```

Loom73 ships with the Plain theme:

```env
LOOM73_THEME='plain'
```

Theme entry files are discovered by convention and compiled to standalone public stylesheets. Invalid or unavailable theme identifiers fall back to Plain.

The theme contract deliberately excludes:

```text
theme inheritance
child themes
template overrides
database theme selection
plugin lifecycle
```

Optional bundle-size guardrails remain a possible later improvement and are not a 6.1 release requirement.

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
### Frontend confidence

Add focused browser automation around the public frontend contracts:

```text
Playwright functional smoke tests
responsive viewport checks
navigation and component interactions
selected visual regression snapshots
@axe-core/playwright accessibility scans
```

Accessibility scans should cover both the initial document and meaningful interactive states, including:

```text
expanded responsive navigation
open dialogs
visible tooltips
form validation feedback
sorted and paginated tables
revealed InView content
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
frontend functional smoke tests
automated accessibility checks
deployment validation
```
## 6.5 — Public Discovery

Make search-engine exposure an explicit choice of each installed instance.

### Indexing control

Indexing is disabled by default through two complementary mechanisms:

```text
robots.txt
    controls crawler access

X-Robots-Tag
    controls indexing through the HTTP response
```
The versioned `.htaccess` checks for this instance-owned marker: 
```text
storage/.loom73-indexing-enabled
```

Possible shuttle operations: 
```text
php shuttle indexing.info
php shuttle indexing.enable
php shuttle indexing.disable
```
The commands should coordinate both `robots.txt` and the `.loom73-indexing-enabled` marker.

```text
indexing.info
    report the marker state
    inspect the global robots policy
    warn when the two mechanisms disagree

indexing.enable
    configure robots.txt to allow crawling
    create the indexing marker

indexing.disable
    remove the indexing marker
    configure robots.txt with Disallow: /
```
Implementation should:

- default to indexing disabled
- preserve unrelated robots.txt directives where possible
- write robots.txt atomically
- report unwritable files clearly
- leave the instance in the safer non-indexable state after partial failure

### Sitemap Generation
Add a small, explicit sitemap generator for public application resources.

The generator should support:

- explicit static routes
- application-provided URL sources
- instance URL from configuration
- optional last-modified dates
- URL normalization and deduplication
- valid XML generation
- atomic file replacement

Possible Shuttle operations:

```text
php shuttle sitemap.generate
php shuttle sitemap.info
```

The generator must not discover controller methods automatically, infer public access from route names or include protected resources.


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
