<?php

use Tester\Environment;

ini_set('html_errors', FALSE); // we don't want HTML errors in console

$baseDir = __DIR__ . '/../../';
require $baseDir . 'libs/composer/autoload.php';
Environment::setup();


define('TEMP_DIR', __DIR__ . '/../temp');
@mkdir(TEMP_DIR . '/cache', 0777, TRUE); // @ - dir may already exist
$loader = new Nette\Loaders\RobotLoader;
$loader
    ->setCacheStorage(new Nette\Caching\Storages\FileStorage(TEMP_DIR))
    ->addDirectory($baseDir . 'app')
    ->addDirectory($baseDir . 'tests')
    ->register();
