cd "%~dp0\.."
php "www\index.php" migrations:migrate --no-interaction
cd "%~dp0"