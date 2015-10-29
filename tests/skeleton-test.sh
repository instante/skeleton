#!/usr/bin/env bash
pushd "$(dirname "$0")/.."

cp ./tests/php-unix.ini ./tests/php.ini
PHP_EXT=`php -r "echo ini_get('extension_dir');"`
echo "" >> ./tests/php.ini # empty line
echo "extension_dir=$PHP_EXT" >> ./tests/php.ini

echo -e "test@doe.com\nfoo/bar\ndescriptiontest\nlicensetest\nvertest\nauthorname\nauthormail\n" | php ./bin/deployment/init-project.php

if [ $? -ne 0 ]; then
    >&2 echo "failed executing init-project.php: returned exitcode $?"
    popd
    exit 1
fi
>&2 echo "project initialization script done"

if [ `cat app/config/default.neon | grep "test@doe.com" | wc -l` != 1 ]; then
    >&2 echo "failed: app/config/default.neon does not contain set e-mail"
    popd
    exit 2
fi
>&2 echo "webmaster e-mail written"

if [ `cat composer.json | grep "foo/bar" | wc -l` != 1 \
    -o `cat composer.json | grep "descriptiontest" | wc -l` != 1 \
    -o `cat composer.json | grep "licensetest" | wc -l` != 1 \
    -o `cat composer.json | grep "vertest" | wc -l` != 1 \
    -o `cat composer.json | grep "authorname" | wc -l` != 1 \
    -o `cat composer.json | grep "authormail" | wc -l` != 1 \
    ]; then
    >&2 echo "failed: composer.json does not contain one of package name, description, license, author name, author e-mail or version"
    popd
    exit 3
fi
>&2 echo "composer.json configured"

if [ `cat frontend/package.json | grep "foo/bar" | wc -l` != 1 \
    -o `cat frontend/package.json | grep "descriptiontest" | wc -l` != 1 \
    -o `cat frontend/package.json | grep "licensetest" | wc -l` != 1 \
    -o `cat frontend/package.json | grep "vertest" | wc -l` != 1 \
    -o `cat frontend/package.json | grep "authorname" | wc -l` != 1 \
    ]; then
    >&2 echo "failed: frontend/package.json does not contain one of package name, description, license, author name or version"
    popd
    exit 3
fi
>&2 echo "frontend/package.json configured"

if [ `cat frontend/bower.json | grep "foo/bar" | wc -l` != 1 \
    -o `cat frontend/bower.json | grep "descriptiontest" | wc -l` != 1 \
    -o `cat frontend/bower.json | grep "licensetest" | wc -l` != 1 \
    -o `cat frontend/bower.json | grep "vertest" | wc -l` != 1 \
    -o `cat frontend/bower.json | grep "authorname" | wc -l` != 1 \
    ]; then
    >&2 echo "failed: frontend/bower.json does not contain one of package name, description, license, author name or version"
    popd
    exit 4
fi
>&2 echo "frontend/bower.json configured"

./libs/composer/bin/tester ./tests -p php -c ./tests
EXITCODE=$?

popd

exit "$EXITCODE"
