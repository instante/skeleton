#!/usr/bin/env bash
pushd "$(dirname "$0")/.."

cp ./tests/php-unix.ini ./tests/php.ini
PHP_EXT=`php -r "echo ini_get('extension_dir');"`
echo "" >> ./tests/php.ini # empty line
echo "extension_dir=$PHP_EXT" >> ./tests/php.ini

./libs/composer/bin/tester ./tests -p php -c ./tests

popd
