<?php
if (PHP_VERSION_ID < 50600) {
    // Application may crash on parse errors on insufficient PHP version.
    // Rather give user-friendly error message here.
    die ('Instante needs PHP version 5.6+');
}

if (file_exists($mt = __DIR__ . '/.maintenance.php')) {
    // Maintenance mode active - prevent access to the application
    require $mt;
    exit;
}

// Let bootstrap create DI container.
$container = require __DIR__ . '/../app/bootstrap.php';

// Run application.
$container->getService('application')->run();
