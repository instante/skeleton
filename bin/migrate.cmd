@ECHO OFF
pushd "%~dp0\.."

IF EXIST migrations\Version*.php (
    echo "executing migrations..."
    php "www\index.php" migrations:migrate --no-interaction
) ELSE (
    echo "no migrations found, skipping..."
)
popd