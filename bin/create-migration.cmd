call migrate
pushd "%~dp0\.."
del /s /q "temp\cache"
del /s /q "temp\proxies"
del "temp\btfj.dat"
php "www\index.php" orm:generate-proxies
php "www\index.php" migrations:diff
popd