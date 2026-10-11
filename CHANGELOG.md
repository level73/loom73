# Changelog

## 6.3.0 — Unreleased

### Added

- `loom73.update` plans and applies ordered core database updates, records successful scripts, and stops on failure.
- Existing 6.2.0 databases can register a baseline after core schema verification.
- Applications can override core configuration through `application/config/modules/` and explicitly map controller routes.

### Changed

- Upgradeable core controllers and the User model now live under `lib/`.
- Application controllers take precedence over core routes; views remain under `application/views/`.

### Compatibility

- Version 6.3.0 includes no new database schema scripts. Existing 6.2.0 instances should run `php shuttle loom73.update --plan` before applying the baseline.
- Forks that directly reference a moved core controller class must update its namespace. Route URLs remain unchanged.
- Updating application code still requires a reviewed Git merge; `loom73.update` handles database changes only.
