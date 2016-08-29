<?php

use Instante\Bootstrap\Bootstrapper;

$rootDir = __DIR__ . '/..';
$paths = [ //additional paths
    'app' => __DIR__,
    'root' => $rootDir,
    'log' => $rootDir . '/log',
    'www' => $rootDir . '/www',
    'temp' => $rootDir . '/temp',
    'vendor' => $rootDir . '/vendor',
    'config' => __DIR__ . '/config',
];

$autoloadPath = $paths['vendor'] . '/autoload.php';
if (!file_exists($autoloadPath)) {
    die('Please run \'composer install\' first.');
}

require_once $autoloadPath;

return (new Bootstrapper($paths))
    ->addRobotLoadedPaths($paths['app'])
    ->build();
