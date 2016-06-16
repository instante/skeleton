<?php

namespace Instante\Tests;

use Nette\DI\Container;
use Tester\Assert;
use Tester\Environment;

$baseDir = __DIR__ . '/../../';
require $baseDir . 'vendor/autoload.php';
Environment::setup();

$container = require $baseDir . 'app/bootstrap.php';
Assert::type(Container::class, $container);
