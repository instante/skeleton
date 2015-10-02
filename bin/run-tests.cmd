pushd "%~dp0\.."

copy .\tests\php-win.ini .\tests\php.ini
echo "" >> .\tests\php.ini # empty line
php -r "echo 'extension_dir=' . ini_get('extension_dir');" >> .\tests\php.ini

call .\libs\composer\bin\tester .\tests -p php -c .\tests

popd
