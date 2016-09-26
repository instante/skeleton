#!/usr/bin/env bash
pushd "$(dirname "$0")/.."

rm -rf temp/deploytest
git checkout-index --prefix=temp/deploytest/ -a
cd temp/deploytest
composer install --no-interaction
./vendor/bin/parallel-lint -e php,phpt --exclude vendor .

if [ $? != 0 ]; then
    >&2 echo "failed: lint failed"
    popd
    exit 8
fi

echo -e "test@doe.com\nfoo/bar\ndescriptiontest\nlicensetest\nvertest\nauthorname\nauthormail\n" | php ./bin/deployment/init-project.php 1> /dev/null

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
    -o `cat composer.json | grep "authorname" | wc -l` != 1 \
    -o `cat composer.json | grep "authormail" | wc -l` != 1 \
    ]; then
    >&2 echo "failed: composer.json does not contain one of package name, description, license, author name or author e-mail"
    popd
    exit 3
fi
>&2 echo "composer.json configured"

if [ `cat frontend/package.json | grep "foo.bar" | wc -l` != 1 \
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

echo -e "d\ny\ntravis\n\ninstante\ninstante_test\n127.0.0.1\n" | php ./bin/deployment/deploy-project.php 1> /dev/null

if [ `cat app/config/environment` != "development" ]; then
    >&2 echo "failed: environment not set to development"
    popd
    exit 5
fi
>&2 echo "environment set to development"

if [ ! -f app/config/local.neon ]; then
    >&2 echo "failed: local.neon not created"
    popd
    exit 6
fi

if ! cmp app/config/local.neon tests/skeleton/local.neon.expected >/dev/null 2>&1; then
    >&2 echo "failed: local.neon does not match tests/skeleton/local.neon.expected"
    popd
    exit 7
fi
>&2 echo "local.neon configured properly"



./vendor/bin/tester ./tests -p php
EXITCODE=$?

cd ..
rm -rf deploytest

popd

exit "$EXITCODE"
