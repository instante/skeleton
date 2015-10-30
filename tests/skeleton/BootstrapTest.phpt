<?php

namespace Instante\Tests;

use Nette\DI\Container;
use Tester\Assert;
use Tester\Environment;

ini_set('html_errors', FALSE); // we don't want HTML errors in console

$baseDir = __DIR__ . '/../../';
require $baseDir . 'vendor/autoload.php';
Environment::setup();

$container = require $baseDir . 'app/bootstrap.php';
Assert::type(Container::class, $container);
