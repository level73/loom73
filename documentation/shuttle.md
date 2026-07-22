# Loom73 Shuttle

Shuttle is the Loom73 command-line tool.

It provides quick operational commands for installing, inspecting and maintaining a Loom73 instance from the CLI.

Shuttle belongs to the `Loom73\Shuttle` namespace and follows the same general principle as the rest of the blueprint:

```text
Small tools.
Clear conventions.
No unnecessary machinery.
```

Shuttle is not only a database utility. It is the operational bridge between deployed code and a usable Loom73 instance.

---

## Running Shuttle

Shuttle commands should be run from the application root.

```bash
cd /var/www/example.com
php shuttle <command>
```

On deployed instances, Shuttle should normally be run as the deployment user, for example:

```bash
sudo -iu deploy
cd /var/www/example.com
php shuttle <command>
```

Avoid running Shuttle as `root` unless a command explicitly requires it.

Avoid running Shuttle as `www-data`. The web server user should run the application, not administer the instance.

---

## Available commands

At this stage Shuttle supports three main commands.

---

### `loom73.info`

Returns basic version information about Loom73 and Shuttle.

```bash
php shuttle loom73.info
```

This command is useful to quickly verify that the CLI is reachable and that the installed blueprint version is the expected one.

---

### `loom73.install`

Performs a clean Loom73 installation.

```bash
php shuttle loom73.install
```

This command initializes the instance.

Depending on the current project configuration, it may:

```text
create database tables
drop and recreate existing tables during development
insert required seed data
create runtime directories
prepare storage/uploads
prepare storage/logs
prepare storage/cache
```

`loom73.install` is intentionally broader than a database installer. The command installs the runtime shape of the instance, not only its schema.

The guiding rule is:

```text
Deploy code.
Install runtime state.
```

Runtime directories such as `storage/` are not deployed from GitHub and are not part of the repository. They belong to the live instance and are created by Shuttle.

Expected runtime structure:

```text
storage/
  uploads/
  logs/
  cache/
```

---

### `user.admin`

Creates a new Admin user in the system.

```bash
php shuttle user.admin
```

This command is usually run after `loom73.install`, when the database exists but no administrative user has been created yet.

Typical first-install flow:

```bash
cd /var/www/example.com

php shuttle loom73.install
php shuttle user.admin
```

After creating the Admin user, log into the application and perform a basic smoke test.

---

## Creating new commands

To create a new Shuttle command, add a new PHP file inside the `commands` directory.

The file name defines the command name.

Multiple words must be dot-separated.

Example:

```text
commands/database.update.php
```

This file will be executed as:

```bash
php shuttle database.update
```

---

## Command class naming

The command file must contain a class whose name is the capitalized version of the file name, without dots.

Example:

```text
database.update.php
```

must contain:

```php
DatabaseUpdate
```

Another example:

```text
user.admin.php
```

must contain:

```php
UserAdmin
```

The convention is important because Shuttle uses predictable naming to resolve commands without extra configuration.

---

## Command namespace

Command classes should live under:

```php
Loom73\Shuttle
```

Example:

```php
<?php

namespace Loom73\Shuttle;

class DatabaseUpdate
{
    public function __construct()
    {
        // Command logic here.
    }
}
```

A command class does not strictly need to extend the main `Shuttle` class.

It may extend a base class only when it actually needs shared behavior. The default expectation is simpler:

```text
one command
one file
one class
one constructor-driven operation
```

---

## Command execution model

Inside the command class, define a constructor and place the command logic there.

Example:

```php
<?php

namespace Loom73\Shuttle;

class Loom73Info
{
    public function __construct()
    {
        echo "Loom73 version: 6.0.0" . PHP_EOL;
        echo "Shuttle version: 1.0.0" . PHP_EOL;
    }
}
```

This keeps commands small and direct.

For larger commands, the constructor may delegate to private methods:

```php
<?php

namespace Loom73\Shuttle;

class DatabaseUpdate
{
    public function __construct()
    {
        $this->run();
    }

    private function run(): void
    {
        // Command flow here.
    }
}
```

The constructor should remain readable. If a command grows too much, split the logic into clearly named private methods or reusable Shuttle helpers.

---

## The CLI helper

Shuttle includes a small `CLI` helper class for terminal output and interactive prompts.

It can be used to:

```text
colorize CLI output
ask confirmation questions
handle simple interactive flows
```

Example:

```php
<?php

namespace Loom73\Shuttle;

class ExampleCommand
{
    public function __construct()
    {
        $cli = new CLI();

        echo $cli->cout_color(
            "Hey there, I'm going to be red!",
            "red"
        );

        if ($cli->confirm("Are you sure you want to do this?")) {
            echo "Confirmed." . PHP_EOL;
        }
    }
}
```

The CLI helper is intentionally small. It is there to make command output readable, not to become a full console framework.

---

## Command design principles

Shuttle commands should be:

```text
explicit
small
safe by default
easy to run again when possible
clear about destructive operations
```

Commands that drop tables, reset data or delete files should ask for confirmation unless they are clearly designed for automated development environments.

A command should tell the operator what it is about to do.

Good command behavior:

```text
show the operation
ask when destructive
fail loudly when configuration is missing
print a clear success message
```

Bad command behavior:

```text
silently delete data
hide errors
depend on web sessions
require browser context
```

---

## Deployment relationship

Shuttle complements the deployment workflow.

Deployment sends code to the server.

Shuttle prepares the instance.

The separation is important:

```text
GitHub Actions
  builds and deploys code

Shuttle
  installs runtime state

The VPS
  stores .env, database, uploads, logs and cache
```

`storage/` should not be synced during deploy.

`config/.env` should not be synced during deploy.

Both belong to the server instance.

---

## Summary

```text
php shuttle loom73.info
  shows Loom73 and Shuttle version information

php shuttle loom73.install
  installs database structure and runtime directories

php shuttle user.admin
  creates a new Admin user
```

To add a new command:

```text
create commands/my.command.php
define Loom73\Shuttle\MyCommand
run php shuttle my.command
```

Shuttle keeps Loom73 operational without turning the blueprint into a heavy framework.
