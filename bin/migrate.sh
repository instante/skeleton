cd "$(dirname "$0")"
cd ".."
php www/index.php migrations:migrate --no-interaction
cd "$(dirname "$0")"