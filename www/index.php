<?php
if (PHP_VERSION_ID < 50600) {
    // Application may crash on parse errors on insufficient PHP version.
    // Rather give user-friendly error message here.
    die ('Instante needs PHP version 5.6+');
}
// Uncomment this line if you must temporarily take down your site for maintenance.
// require '.maintenance.php';
// Let bootstrap create Dependency Injection container.
$container = require __DIR__ . '/../app/bootstrap.php';

// Run application.
$container->getService('application')->run();
