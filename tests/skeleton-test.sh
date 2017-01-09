#!/usr/bin/env bash
cd "$(dirname "$0")/.."

rm -rf temp/deploytest
git checkout-index --prefix=temp/deploytest/ -a
cd temp/deploytest
composer install --no-interaction
./vendor/bin/parallel-lint -e php,phpt --exclude vendor .

if [ $? != 0 ]; then
    >&2 echo "failed: lint failed"
    exit 8
fi

echo -e "test@doe.com\nfoo/bar\ndescriptiontest\nlicensetest\nvertest\nauthorname\nauthormail\nsass\n" | php ./bin/deployment/init-project.php
EXITCODE = $?

if [ "$EXITCODE" -ne 0 ]; then
    >&2 echo "failed executing init-project.php: returned exitcode $EXITCODE"
    exit 1
fi
>&2 echo "project initialization script done"

if [ `cat app/config/default.neon | grep "test@doe.com" | wc -l` != 1 ]; then
    >&2 echo "failed: app/config/default.neon does not contain set e-mail"
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
    exit 4
fi
>&2 echo "frontend/bower.json configured"

DEPLOYSCRIPT="../../tests/skeleton/deploy-script-input"
if [ -f "$DEPLOYSCRIPT" ]; then
    cat "$DEPLOYSCRIPT" | php ./bin/deployment/deploy-project.php
else
    echo "Unable to test, tests/skeleton/deploy-script-input is missing"
    exit 9
fi

if [ `cat app/config/environment` != "development" ]; then
    >&2 echo "failed: environment not set to development"
    exit 5
fi
>&2 echo "environment set to development"

if [ ! -f app/config/local.neon ]; then
    >&2 echo "failed: local.neon not created"
    exit 6
fi

if [ `cat app/config/local.neon | grep "secure: true" | wc -l` != 1 ]; then
    >&2 echo "failed: local.neon hasn't configured secure routes properly"
    exit 7
fi
>&2 echo "local.neon configured properly"

if [ `cat frontend/gulpfile.babel.js | grep "less" | wc -l` != 0 \
    ]; then
    >&2 echo "failed: frontend/gulpfile.babel.js contains css preprocessor that have to be removed"
    exit 4
fi
>&2 echo "frontend/gulpfile.babel.js configured"


./vendor/bin/tester ./tests -p php
EXITCODE=$?

cd ..

if [ "$EXITCODE" -eq 0 ]; then
    rm -rf deploytest
fi

exit "$EXITCODE"
