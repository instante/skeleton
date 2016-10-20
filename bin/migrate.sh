#!/usr/bin/env bash
pushd "$(dirname "$0")/.." > /dev/null

if [ `ls -la migrations/Version*.php 2>/dev/null | wc -l` -gt 0 ]; then
    echo "executing migrations..."
    php www/index.php migrations:migrate --no-interaction
else
    echo "no migrations found, skipping..."
fi
popd > /dev/null
