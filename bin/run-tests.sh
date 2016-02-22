#!/usr/bin/env bash
pushd "$(dirname "$0")/.."

if [ -f "./tests/php-local.ini" ]; then
    cp ./tests/php-local.ini ./tests/php.ini
else
    cp ./tests/php-unix.ini ./tests/php.ini
    PHP_EXT=`php -r "echo ini_get('extension_dir');"`
    echo "" >> ./tests/php.ini # empty line
    echo "extension_dir=$PHP_EXT" >> ./tests/php.ini
fi

case "$1" in
"")
    TESTS_DIR="./tests/"
    ;;
"u")
    TESTS_DIR="./tests/unit/"
    ;;
"i")
    TESTS_DIR="./tests/integration/"
    ;;
*)
    TESTS_DIR="$1"
    ;;
esac

./vendor/bin/tester ./tests/$1 -p php -c ./tests
EXITCODE=$?

popd

exit "$EXITCODE"
