# Loom73 Deployment Guide

This document describes the recommended deployment procedure for a Loom73-based instance.

Loom73 is designed as a forkable blueprint. Each project instance may fork the main repository and keep the same deployment workflow, while using its own server, domain, database, `.env` file, storage, and GitHub repository secrets.

---

## 1. Deployment model

Loom73 uses a build-and-sync deployment model:

```text
GitHub Actions
  checkout repository
  install Composer dependencies
  install Node dependencies
  build frontend assets
  rsync application files to VPS
```

The VPS does **not** need to clone the Git repository.

The VPS receives the built application files via `rsync`.

The following elements are server/runtime state and are not deployed from GitHub:

```text
config/.env
storage/
database
uploaded files
logs
cache
```

---

## 2. Recommended server structure

For a domain such as:

```text
loom73.lvl73.it
```

the application root should be:

```text
/var/www/loom73.lvl73.it
```

The deployed structure should look like this:

```text
/var/www/loom73.lvl73.it/
  application/
  auth/
  commands/
  config/
    .env
    .env.example
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
```

The web server document root must point to:

```text
/var/www/loom73.lvl73.it/public_html/public
```

The browser must never directly access:

```text
application/
auth/
commands/
config/
lib/
storage/
vendor/
```

---

## 3. Apache virtual host example

```apache
<VirtualHost *:80>
    ServerName loom73.lvl73.it

    DocumentRoot /var/www/loom73.lvl73.it/public_html/public

    <Directory /var/www/loom73.lvl73.it/public_html/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/loom73.lvl73.it-error.log
    CustomLog ${APACHE_LOG_DIR}/loom73.lvl73.it-access.log combined
</VirtualHost>
```

If the instance is public but used only for test/development, protect it with Basic Auth and prevent indexing.

Example `robots.txt`:

```txt
User-agent: *
Disallow: /
```

Example `.htaccess` additions:

```apache
<IfModule mod_headers.c>
    Header set X-Robots-Tag "noindex, nofollow, noarchive"
</IfModule>
```

---

## 4. Deployment user

Create a dedicated SSH user for deployment, for example:

```text
deploy
```

Avoid deploying as:

```text
root
www-data
personal user
```

The recommended ownership model is:

```text
deploy:www-data
```

where:

```text
deploy   updates application files
www-data runs PHP/Apache and writes runtime files
```

Initial setup:

```bash
sudo chown -R deploy:www-data /var/www/loom73.lvl73.it
sudo find /var/www/loom73.lvl73.it -type d -exec chmod 2775 {} \;
sudo find /var/www/loom73.lvl73.it -type f -exec chmod 664 {} \;
```

The `2775` permission on directories enables the setgid bit, helping new directories inherit the `www-data` group.

Make sure `deploy` can write inside the application root:

```bash
sudo -iu deploy

cd /var/www/loom73.lvl73.it
mkdir test-write
rmdir test-write
```

---

## 5. GitHub repository secrets

Each Loom73 fork/instance should define its own repository secrets.

In GitHub:

```text
Repository → Settings → Secrets and variables → Actions → Repository secrets
```

Required secrets:

```text
SSH_HOST
SSH_USER
SSH_PORT
SSH_PRIVATE_KEY
DEPLOY_PATH
```

Example:

```text
SSH_HOST=123.123.123.123
SSH_USER=deploy
SSH_PORT=22
DEPLOY_PATH=/var/www/loom73.lvl73.it
```

`SSH_PRIVATE_KEY` must contain the full private key:

```text
-----BEGIN OPENSSH PRIVATE KEY-----
...
-----END OPENSSH PRIVATE KEY-----
```

The public key must be installed on the VPS in:

```text
/home/deploy/.ssh/authorized_keys
```

Test SSH access locally before deploying:

```bash
ssh -i loom73_deploy_key deploy@SERVER_IP
```

---

## 6. Deployment exclusions

The repository should include a file:

```text
deploy/rsync-exclude.txt
```

Recommended content:

```text
.git/
.github/

.idea/
.DS_Store
Thumbs.db

node_modules/
frontend-src/

config/.env
.env

storage/

frontend-src/concat.css

npm-debug.log*
yarn-debug.log*
yarn-error.log*
```

Important rule:

```text
storage/ is runtime state.
It is never synced by deployment.
It is created by Shuttle during instance installation.
```

This prevents core updates from overwriting uploaded files or resetting runtime permissions.

---

## 7. After-deploy script

The repository should include:

```text
deploy/after-deploy.sh
```

Recommended minimal version:

```bash
#!/usr/bin/env bash

set -e

APP_PATH="${1:-.}"

cd "$APP_PATH"

echo "After deploy completed for $APP_PATH"

if [ -d storage ]; then
    echo "Runtime storage found:"
    ls -ld storage storage/uploads storage/logs storage/cache 2>/dev/null || true
else
    echo "Runtime storage not found."
    echo "Run Shuttle install to initialize this instance."
fi
```

Make it executable:

```bash
chmod +x deploy/after-deploy.sh
```

The deploy script should not create or reset `storage/`. Runtime directories belong to the installation process.

---

## 8. GitHub Actions workflow

The workflow should live in:

```text
.github/workflows/deploy-test.yml
```

Example:

```yaml
name: Deploy test

on:
  push:
    branches:
      - main
      - master

jobs:
  deploy:
    name: Build and deploy
    runs-on: ubuntu-latest

    steps:
      - name: Checkout repository
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
          extensions: pdo, pdo_mysql, fileinfo
          coverage: none

      - name: Install Composer dependencies
        run: composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

      - name: Setup Node
        uses: actions/setup-node@v4
        with:
          node-version: '24'
          cache: npm

      - name: Install Node dependencies
        run: npm ci

      - name: Build frontend assets
        run: npm run build:deploy

      - name: Prepare SSH
        run: |
          mkdir -p ~/.ssh
          echo "${{ secrets.SSH_PRIVATE_KEY }}" > ~/.ssh/deploy_key
          chmod 600 ~/.ssh/deploy_key
          ssh-keyscan -p "${{ secrets.SSH_PORT }}" "${{ secrets.SSH_HOST }}" >> ~/.ssh/known_hosts

      - name: Deploy via rsync
        run: |
          rsync -az --delete \
            --exclude-from=deploy/rsync-exclude.txt \
            -e "ssh -i ~/.ssh/deploy_key -p ${{ secrets.SSH_PORT }}" \
            ./ \
            "${{ secrets.SSH_USER }}@${{ secrets.SSH_HOST }}:${{ secrets.DEPLOY_PATH }}/"

      - name: Run after-deploy script
        run: |
          ssh -i ~/.ssh/deploy_key -p "${{ secrets.SSH_PORT }}" \
            "${{ secrets.SSH_USER }}@${{ secrets.SSH_HOST }}" \
            "bash ${{ secrets.DEPLOY_PATH }}/deploy/after-deploy.sh ${{ secrets.DEPLOY_PATH }}"
```

---

## 9. Environment file

The real environment file must exist only on the VPS:

```text
/var/www/loom73.lvl73.it/config/.env
```

It should be created manually from:

```text
config/.env.example
```

The file `config/.env` must never be committed and must never be deployed by GitHub Actions.

---

## 10. Installing a new Loom73 instance

After the first successful deploy, connect to the VPS as the deploy user:

```bash
ssh deploy@SERVER_IP
```

Then:

```bash
cd /var/www/loom73.lvl73.it
php shuttle loom73.install
```
This will use Loom73's internal CLI tool to run basic setup.

Shuttle install is responsible for initializing the instance:

```text
database tables
seed data
runtime storage directories
```

Shuttle should be run as:

```text
deploy
```

not as `root` and not as `www-data`.

After running the install script, setup the instance with an admin user:

```text
php shuttle user.admin
```

---

## 11. Runtime storage

`storage/` is not part of the Git repository and is not deployed.

It should be created by Shuttle during installation:

```text
storage/
  uploads/
  logs/
  cache/
```

Expected permissions:

```text
drwxrwsr-x deploy www-data storage
drwxrwsr-x deploy www-data uploads
drwxrwsr-x deploy www-data logs
drwxrwsr-x deploy www-data cache
```

Check with:

```bash
ls -ld /var/www/loom73.lvl73.it/storage
ls -ld /var/www/loom73.lvl73.it/storage/uploads
ls -ld /var/www/loom73.lvl73.it/storage/logs
ls -ld /var/www/loom73.lvl73.it/storage/cache
```

If needed, reset permissions manually:

```bash
sudo chown -R deploy:www-data /var/www/loom73.lvl73.it/storage
sudo find /var/www/loom73.lvl73.it/storage -type d -exec chmod 2775 {} \;
sudo find /var/www/loom73.lvl73.it/storage -type f -exec chmod 664 {} \;
```

---

## 12. Smoke test checklist

After installation or deployment, verify:

```text
homepage or login page loads
CSS and JS load correctly
pretty URLs / rewrite rules work
database connection works
admin user can log in
profile page loads
avatar upload works
asset is stored in storage/uploads
/asset/view/{uuid} returns image content
replacing avatar deactivates the previous asset
new avatar is active in the DB
```

Useful SQL check:

```sql
SELECT
    idasset,
    uuid,
    owner_type,
    owner_id,
    owner_slot,
    asset_type,
    visibility,
    status,
    uploaded_by,
    created_at
FROM assets
ORDER BY idasset DESC;
```

Expected avatar pattern:

```text
owner_type = user
owner_slot = avatar
visibility = restricted
one previous avatar status = 1
latest avatar status = 2
```

---

## 13. Forking model

Loom73 is intended to work as a forkable blueprint.

The main repository contains:

```text
core architecture
deploy workflow
frontend build process
Shuttle installer
documentation
default configuration examples
```

Each fork/instance provides:

```text
GitHub repository secrets
server .env file
database
runtime storage
project-specific application code
domain and virtual host
```

When the upstream blueprint releases a new version, a fork can update from upstream and keep its own deployment secrets and runtime data.

Recommended pattern:

```bash
git remote add upstream git@github.com:level73/loom73.git
git fetch upstream --tags
git merge v6.x.x
```

Prefer updating from tagged releases rather than blindly merging upstream `main`.

---

## 14. Key principles

```text
DEPLOY_PATH = application root
DocumentRoot = DEPLOY_PATH/public_html/public

storage/ = runtime state
config/.env = server-only secret
frontend-src/ = build source, not deployed
vendor/ = built by GitHub Actions and deployed
node_modules/ = never deployed
database = managed by Shuttle/install process
```

The core deployment rule is:

```text
Deploy code.
Install runtime state.
Never overwrite instance data during core updates.
```
