#!/usr/bin/env bash
pushd "$(dirname "$0")"

./migrate.sh
./purge-cache.sh

cd ".."
php www/index.php orm:generate-proxies
php www/index.php migrations:diff

popd
