#!/usr/bin/env bash
pushd "$(dirname "$0")/.."

if [ -f "./tests/php-local.ini" ]; then
    cp ./tests/php-local.ini ./tests/php.ini
elif [ -f ./tests/php-unix.ini ]; then
    cp ./tests/php-unix.ini ./tests/php.ini
    echo "" >> ./tests/php.ini # empty line
    PHP_EXT=`php -r "echo ini_get('extension_dir');"`
    echo "extension_dir=$PHP_EXT" >> ./tests/php.ini
elif [ -f "./tests/php.ini" ]; then # remove old php.ini from tests
    rm "./tests/php.ini"
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

rm -rf ./tests/temp/*
mkdir -p ./tests/temp/sessions
./vendor/bin/tester "./$TESTS_DIR" -p php -c ./tests
EXITCODE=$?

popd

exit "$EXITCODE"
