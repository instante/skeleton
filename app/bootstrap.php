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

require_once $paths['vendor'] . '/autoload.php';

return (new Bootstrapper($paths))
    ->addRobotLoadedPaths($paths['app'])
    ->build();
