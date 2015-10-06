pushd "%~dp0"

call migrate
call purge-cache

cd ..
php "www\index.php" orm:generate-proxies
php "www\index.php" migrations:diff

popd
