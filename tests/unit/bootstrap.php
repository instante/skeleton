<?php

ini_set('html_errors', FALSE); // we don't want HTML errors in console

$baseDir = __DIR__ . '/../../';
require $baseDir . 'libs/composer/autoload.php';
Tester\Environment::setup();

$loader = new Nette\Loaders\RobotLoader;
$loader
    ->setCacheStorage(new Nette\Caching\Storages\FileStorage($baseDir . 'temp/cache'))
    ->addDirectory($baseDir . 'app')
    ->addDirectory($baseDir . 'tests')
    ->register();
