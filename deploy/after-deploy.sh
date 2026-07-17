#!/usr/bin/env bash

set -e

APP_PATH="${1:-.}"

cd "$APP_PATH"

mkdir -p storage/uploads
mkdir -p storage/logs
mkdir -p storage/cache

chmod -R ug+rwX storage

find storage -type d -exec chmod 2775 {} \;
find storage -type f -exec chmod 664 {} \;

echo "After deploy completed for $APP_PATH"