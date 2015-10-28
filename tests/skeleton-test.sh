#!/usr/bin/env bash
pushd "$(dirname "$0")/.."

cp ./tests/php-unix.ini ./tests/php.ini
PHP_EXT=`php -r "echo ini_get('extension_dir');"`
echo "" >> ./tests/php.ini # empty line
echo "extension_dir=$PHP_EXT" >> ./tests/php.ini

php ./bin/deployment/init-project.php < "test@doe.com\nfoo/bar\ndescription\nlic\nver\nauthorname\nauthormail\n"

if [ $? -ne 0 ]; then
    popd
    exit 1
fi

if [ `cat app/config/default.neon | grep "test@doe.com" | wc -l` != 1 ]; then
    popd
    exit 2
fi

popd
