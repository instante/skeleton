#!/usr/bin/env sh

# This script is used to deploy changes from repository (the project has to be configured locally before).
# Add any re-deploy build steps of your application there.

cd "$(dirname "$0")/.."

cp app/.maintenance.php www/.maintenance.php

git pull
composer install
bin/purge-cache.sh
bin/migrate.sh

if command -v npm > /dev/null; then
    cd frontend
    npm install
    if command -v bower > /dev/null; then
        bower install
    else
        npm install bower
        node_modules/bower/bin/bower install
    fi

    if command -v grunt > /dev/null; then
        grunt dist
    else
        npm install grunt-cli
        node_modules/grunt-cli/bin/grunt dist
    fi
    cd ..
else
    echo "npm not installed, skipping frontend compilation"
fi

rm -f www/.maintenance.php
