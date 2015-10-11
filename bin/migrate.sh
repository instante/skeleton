#!/usr/bin/env bash
pushd "$(dirname "$0")/.."

if [ -f migrations/Version*.php ]; then
    echo "executing migrations..."
    php www/index.php migrations:migrate --no-interaction
else
    echo "no migrations found, skipping..."
fi
popd