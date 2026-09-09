# Loom73 Deployment Guide

This guide describes the current build-and-sync deployment model for a Loom73 instance.

Loom73 is designed to be forked. Each application may keep the shared workflow while using its own repository, domain, configuration, database, storage and deployment credentials.

## Requirements

The current deployment baseline is:

```text
PHP 8.3 or newer
PHP 8.4 recommended
PDO
PDO MySQL
Fileinfo
Composer
Node.js 24 in the supplied workflow
Apache with mod_rewrite
rsync
SSH access
```

The supplied Apache configuration also uses:

```text
mod_headers
mod_expires
```

for indexing and cache headers.

## Deployment model

The current workflow performs:

```text
GitHub Actions
  → checkout
  → install production Composer dependencies
  → install Node dependencies
  → build frontend assets
  → rsync the built application to the VPS
  → run deploy/after-deploy.sh
```

The VPS does not need a Git checkout.

Code and built dependencies are deployed. Instance-owned state is preserved on the server.

## Server structure

For an instance at:

```text
/var/www/example.com
```

the expected shape is:

```text
application/
commands/
config/
    .env
    example.env
    modules/
deploy/
documentation/
lib/
public_html/
    public/
        index.php
        .htaccess
        robots.txt
        css/
        js/
        assets/
storage/
    uploads/
    logs/
    cache/
vendor/
shuttle
```

The web-server document root must be:

```text
/var/www/example.com/public_html/public
```

The browser must not receive direct access to:

```text
application/
commands/
config/
documentation/
lib/
storage/
vendor/
```

## Environment

The repository provides:

```text
config/example.env
```

Create the real server configuration at:

```text
config/.env
```

For example:

```bash
cd /var/www/example.com
cp config/example.env config/.env
```

Then configure at least:

```text
APPNAME
APPURL
SYSTEM_STATUS
database connection
timezone and locale
session secrets
mail credentials
Yarn storage
```

Use:

```env
SYSTEM_STATUS='production'
DEBUG=0
```

for a production instance.

The real `.env` must never be committed or overwritten by deployment.

## Runtime storage

Runtime files remain outside the public web root.

The standard directories are:

```text
storage/
storage/uploads/
storage/logs/
storage/cache/
```

`storage/cache/` remains part of the installed runtime structure even though Loom73 does not currently provide a generic caching abstraction.

Yarn can receive an absolute storage path through:

```env
YARN_STORAGE_PATH='/var/www/example.com/storage'
```

The upload path remains relative to it:

```env
YARN_UPLOAD_PATH='uploads'
```

The deployment workflow excludes `storage/`. Shuttle creates the standard directories during installation.

## Deployment user and permissions

Use a dedicated deployment account, for example:

```text
deploy
```

A practical ownership model is:

```text
deploy:www-data
```

where the deployment user updates code and the web-server group can read the application.

Runtime directories must also be writable by the process that handles uploads and logs.

Example:

```bash
sudo chown -R deploy:www-data /var/www/example.com

sudo find /var/www/example.com/storage \
    -type d -exec chmod 2770 {} \;

sudo find /var/www/example.com/storage \
    -type f -exec chmod 660 {} \;
```

Set the real environment file separately:

```bash
sudo chown deploy:www-data \
    /var/www/example.com/config/.env

sudo chmod 640 \
    /var/www/example.com/config/.env
```

Exact permission values may vary with the server configuration. The required behavior is:

```text
deploy can update the application
the web server can read application files
the web server can write required runtime directories
unrelated users cannot read secrets
```

## Apache virtual host

A minimal virtual host is:

```apache
<VirtualHost *:80>
    ServerName example.com

    DocumentRoot /var/www/example.com/public_html/public

    <Directory /var/www/example.com/public_html/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/example.com-error.log
    CustomLog ${APACHE_LOG_DIR}/example.com-access.log combined
</VirtualHost>
```

Enable the required modules:

```bash
sudo a2enmod rewrite headers expires
sudo systemctl reload apache2
```

TLS termination and certificate management depend on the host and are outside the repository workflow.

## GitHub repository secrets

Each fork defines its own Actions secrets:

```text
SSH_HOST
SSH_USER
SSH_PORT
SSH_PRIVATE_KEY
DEPLOY_PATH
```

Example values:

```text
SSH_HOST=203.0.113.10
SSH_USER=deploy
SSH_PORT=22
DEPLOY_PATH=/var/www/example.com
```

Install the matching public key in:

```text
/home/deploy/.ssh/authorized_keys
```

Test the deployment account before running the workflow:

```bash
ssh -i loom73_deploy_key deploy@SERVER_IP
```

Never store a private key, password or instance `.env` in the repository.

## Deployment exclusions

The supplied exclusion list prevents runtime and development state from being synchronized.

Important exclusions include:

```text
.git/
.github/
node_modules/
frontend-src/
config/.env
storage/
```

The workflow builds assets before deployment, so compiled files under:

```text
public_html/public/css/
public_html/public/js/
public_html/public/assets/
```

remain part of the deployed result.

Composer production dependencies are installed in CI and the resulting `vendor/` directory is deployed.

## Workflow

The supplied workflow lives at:

```text
.github/workflows/deploy-test.yml
```

It currently runs for pushes to:

```text
main
master
```

and uses:

```text
PHP 8.3
Node.js 24
composer install --no-dev
npm ci
npm run build:deploy
rsync --delete
```

Because `rsync` uses `--delete`, anything inside the deployment path that is absent from the build may be removed unless explicitly excluded.

Before adding instance-owned files or directories, add them to:

```text
deploy/rsync-exclude.txt
```

## After-deploy script

After synchronization, the workflow runs:

```text
deploy/after-deploy.sh
```

The current script confirms the application path and reports whether the standard storage directories exist.

It does not install the database, create runtime state or repair permissions.

That separation is deliberate:

```text
after-deploy
    inspect the deployed instance

loom73.install
    initialize a new instance
```

## First installation

After the first deployment:

```bash
ssh deploy@SERVER_IP
cd /var/www/example.com
```

Create and configure:

```text
config/.env
```

Then inspect the instance:

```bash
php shuttle loom73.info
```

Missing database tables are expected before installation, but the environment and PHP requirements should already be visible.

Run the installer:

```bash
php shuttle loom73.install
```

The command is destructive and intended for a new instance. Read its confirmation prompt carefully.

Create the first administrator:

```bash
php shuttle user.admin
```

Run the health check again:

```bash
php shuttle loom73.info
```

The final result should be:

```text
HEALTHY
```

or a understood `HEALTHY WITH WARNINGS` result.

## Indexing policy

Loom73 ships with indexing disabled.

The repository currently includes:

```text
robots.txt
    User-agent: *
    Disallow: /
```

and `.htaccess` sends:

```http
X-Robots-Tag: noindex, nofollow, noarchive
```

This is the safe default for a blueprint, test instance or private application.

Before publishing a site that should be indexed:

1. update or remove the blocking `robots.txt` directive;
2. remove the `X-Robots-Tag` rule from `.htaccess`;
3. verify the final HTTP headers;
4. confirm that protected routes remain protected independently of indexing rules.

Robots directives are not access control.

## Cache policy

Loom73 currently deploys CSS, JavaScript and images with stable filenames. These assets must be revalidated rather than cached immutably.

The supplied `.htaccess` applies:

```http
Cache-Control: public, max-age=0, must-revalidate
```

to mutable images, CSS and JavaScript.

Fonts may use:

```http
Cache-Control: public, max-age=31536000, immutable
```

provided their filenames are changed when their contents change.

Dynamic HTML and PHP responses use a no-cache policy.

Verify the actual response headers after deployment:

```bash
curl -I https://example.com/css/main.min.css
curl -I https://example.com/js/index.min.js
curl -I https://example.com/assets/fonts/IBM-Plex-Sans_latin_100_700_normal.woff2
```

The configuration only applies when the relevant Apache modules and `.htaccess` overrides are enabled.

## Smoke test

After installation or deployment, verify:

```text
php shuttle loom73.info reports a healthy instance
homepage loads
CSS, JavaScript, fonts and images load
pretty URLs work
an unknown route returns HTTP 404
a missing application view returns HTTP 500
production errors expose no internal path
administrator login works
profile page loads
flash messages survive redirects
avatar upload succeeds
uploaded files remain outside the public web root
public and protected asset delivery behave correctly
Ledger records expected actions
```

Inspect an uploaded asset through its application route rather than through a filesystem URL.

## Updating a fork

Loom73 does not yet provide a finalized automatic update or migration system.

Until the release and upgrade milestone is complete:

```text
review upstream changes
review database changes
test the update on a non-production instance
preserve config/.env and storage/
deploy only after the application-specific merge is understood
```

When tagged releases and `loom73.update` become available, the documentation will define the supported upgrade path.

Do not run `loom73.install` to update an existing production instance.

## Deployment rule

```text
DEPLOY_PATH
    application root

DocumentRoot
    DEPLOY_PATH/public_html/public

config/.env
    server-owned secret

storage/
    server-owned runtime state

frontend-src/
    build source, not deployed

vendor/
    installed in CI and deployed
```

The governing rule is:

```text
Deploy code.
Install runtime state.
Never overwrite instance data during a core update.
```