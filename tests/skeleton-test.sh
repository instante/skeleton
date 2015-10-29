#!/usr/bin/env bash
pushd "$(dirname "$0")/.."

cp ./tests/php-unix.ini ./tests/php.ini
PHP_EXT=`php -r "echo ini_get('extension_dir');"`
echo "" >> ./tests/php.ini # empty line
echo "extension_dir=$PHP_EXT" >> ./tests/php.ini

echo -e "test@doe.com\nfoo/bar\ndescription\nlic\nver\nauthorname\nauthormail\n" | php ./bin/deployment/init-project.php

if [ $? -ne 0 ]; then
    echo "failed executing init-project.php: returned exitcode $?"
    popd
    exit 1
fi

if [ `cat app/config/default.neon | grep "test@doe.com" | wc -l` != 1 ]; then
    echo "failed: app/config/default.neon does not contain set e-mail"
    popd
    exit 2
fi

popd
