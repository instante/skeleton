#!/usr/bin/env bash

# this script is used to deploy changes from repository (the project has to be configured locally before).
# add any re-deploy build steps of your application there.

pushd "$(dirname "$0")/.."

cp app/.maintenance.php www/.maintenance.php

git pull
composer install
bin/purge-cache.sh
bin/migrate.sh

cd frontend
npm install
node_modules/bower/bin/bower install
node_modules/grunt-cli/bin/grunt dist

rm -f www/.maintenance.php

popd
