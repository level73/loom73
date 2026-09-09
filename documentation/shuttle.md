# Loom73 Shuttle

Shuttle is the Loom73 command-line tool.

It installs, inspects and maintains a Loom73 instance through small, explicit commands.

```text
Small tools.
Clear conventions.
No unnecessary machinery.
```

## Running Shuttle

Run commands from the application root:

```bash
cd /var/www/example.com
php shuttle <command>
```

On a deployed instance, use the deployment user. Avoid using `root` or the web-server user for ordinary administration.

```bash
sudo -iu deploy
cd /var/www/example.com
php shuttle loom73.info
```

Shuttle loads:

```text
Composer dependencies
config/system.php
config/labels.php
config/.env
config/modules/*.php
```

before resolving the requested command.

## Available commands

The current command set is:

```text
loom73.info
loom73.install
user.admin
asset_type.new
ledger.cleanup
session.cleaner
```

### `loom73.info`

Inspect the current instance:

```bash
php shuttle loom73.info
```

The command reports:

```text
Loom73 version
SYSTEM_STATUS environment
PHP version

environment-file health
module configuration
database connection
storage directory availability and permissions

required PHP extensions
installed runtime dependency versions

Ledger configuration
Gauge configuration
```

The database check uses:

```php
Connection::ping()
```

which prepares and executes:

```sql
SELECT 1
```

The final status is one of:

```text
HEALTHY
HEALTHY WITH WARNINGS
UNHEALTHY
```

An unhealthy result exits with status code `1`. A healthy result, including one with warnings, exits with status code `0`.

Warnings include incomplete environment values. Errors include a missing `.env`, failed database connection, missing or unwritable runtime directories, missing required extensions, or missing runtime packages.

`Gauge` is reported as `not configured` until the planned component and its configuration are added.

### `loom73.install`

Install the database structure and runtime directories:

```bash
php shuttle loom73.install
```

This is a destructive clean-install command. It asks for confirmation and may drop and recreate Loom73 tables.

It currently initializes:

```text
authentication tables
roles and seed data
asset types
assets
Ledger events
storage/
storage/uploads/
storage/logs/
storage/cache/
```

Use it for a new instance or a deliberately disposable development database.

Do not use it as an update mechanism for an existing production database.

The future release path will distinguish:

```text
loom73.install
    create a new instance

loom73.update
    evolve an existing instance
```

After installation, run:

```bash
php shuttle user.admin
```

### `user.admin`

Create an administrator interactively:

```bash
php shuttle user.admin
```

Shuttle asks for:

```text
username
email
password
```

It creates the user with the administrator role and initializes a session record.

The command requires a working database and should normally be run after `loom73.install`.

### `asset_type.new`

Create a Yarn asset type:

```bash
php shuttle asset_type.new
```

Shuttle asks for:

```text
slug
label
description
```

Asset types provide semantic classifications such as:

```text
image_avatar
project_report
informed_consent
```

They are distinct from owner slots. An owner slot describes where an asset belongs; an asset type describes what the file represents.

### `ledger.cleanup`

Apply the configured Ledger retention policy:

```bash
php shuttle ledger.cleanup
```

The retention period is read from:

```php
Config::get('ledger.retention');
```

Interactive execution displays the configured period and asks for confirmation.

For scheduled or otherwise non-interactive execution, pass:

```bash
php shuttle ledger.cleanup --force
```

To request permanent removal of all Ledger records:

```bash
php shuttle ledger.cleanup --purge
```

For non-interactive purging:

```bash
php shuttle ledger.cleanup --purge --force
```

A retention value of `0` means that automatic retention cleanup is disabled.

The application administrator must configure any cron schedule separately. Shuttle does not install cron entries.

### `session.cleaner`

Run the current PHP-session diagnostic cleaner:

```bash
php shuttle session.cleaner
```

The current command opens the CLI session, invokes PHP session garbage collection, destroys that session and prints diagnostic session data.

It does not purge Loom73 authentication-session rows from the database and should not be documented or scheduled as a complete application-session cleanup mechanism.

Treat it as a development or diagnostic command until a production session-cleanup contract is defined.

## Command resolution

Command files live in:

```text
commands/
```

The filename is the command name:

```text
commands/database.update.php
    → php shuttle database.update
```

Dots separate words when Shuttle resolves the class:

```text
database.update
    → Loom73\Shuttle\DatabaseUpdate
```

The file must contain the resolved class:

```php
<?php

namespace Loom73\Shuttle;

class DatabaseUpdate
{
    public function __construct(?array $args = null)
    {
        $this->run($args ?? []);
    }

    private function run(array $args): void
    {
        // Command logic.
    }
}
```

Shuttle passes command-line arguments after the command name as an array to the constructor.

Existing commands that do not need arguments may ignore it.

A command does not need to extend a common base class.

## CLI helper

The reusable helper is:

```php
Loom73\Shuttle\CLI
```

It currently provides:

```php
cout_color()
confirm()
ask()
```

### Colored output

```php
$CLI = new CLI();

echo $CLI->cout_color(
    'Operation completed.',
    'green'
) . PHP_EOL;
```

Supported named colors include:

```text
red
green
yellow
blue
magenta
cyan
light grey
dark grey
light red
light green
light yellow
light blue
light magenta
light cyan
```

### Questions

Read a free-form answer:

```php
$name = $CLI->ask('Enter the name: ');
```

Request confirmation:

```php
if (!$CLI->confirm('Continue?')):
    echo 'Cancelled.' . PHP_EOL;
    return;
endif;
```

`confirm()` accepts `y`, `yes`, `n` and `no`, repeating the question for any other input.

## Command design

A Shuttle command should:

```text
perform one recognizable operation
make destructive behavior explicit
confirm destructive interactive operations
require explicit flags for destructive automation
return a non-zero exit code on failure
avoid browser or web-session assumptions
avoid exposing passwords and secrets
```

Small commands may keep their flow in the constructor. Larger commands should delegate to private methods.

Shuttle is intentionally small. Shared abstractions should be introduced only after more than one real command needs them.

## Deployment relationship

Deployment and installation are separate operations:

```text
GitHub Actions
    builds and sends application code

Shuttle
    initializes or maintains runtime state

VPS
    owns configuration, database data, uploads and logs
```

The core rule is:

```text
Deploy code.
Install runtime state.
Preserve instance data.
```
