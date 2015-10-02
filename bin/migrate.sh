#!/usr/bin/env bash
pushd "$(dirname "$0")/.."

php www/index.php migrations:migrate --no-interaction
popd