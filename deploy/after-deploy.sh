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