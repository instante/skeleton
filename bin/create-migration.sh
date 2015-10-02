#!/usr/bin/env bash
pushd "$(dirname "$0")"

sh migrate.sh
rm -rf "../temp/cache"
rm -rf "../temp/proxies"
rm "../temp/btfj.dat"
cd ".."
php www/index.php orm:generate-proxies
php www/index.php migrations:diff

popd