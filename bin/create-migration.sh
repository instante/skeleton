#!/usr/bin/env bash
pushd "$(dirname "$0")"

sh migrate.sh
sh purge-cache.sh

cd ".."
php www/index.php orm:generate-proxies
php www/index.php migrations:diff

popd
