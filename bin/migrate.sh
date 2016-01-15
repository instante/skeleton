#!/usr/bin/env bash
pushd "$(dirname "$0")/.."

if [ `ls -la migrations/Version*.php | wc -l` -gt 0 ]; then
    echo "executing migrations..."
    php www/index.php migrations:migrate --no-interaction
else
    echo "no migrations found, skipping..."
fi
popd
