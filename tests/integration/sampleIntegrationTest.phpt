<?php

use Tester\Assert;

$context = require __DIR__ . '/bootstrap.php';
Assert::type(\Nette\DI\Container::class, $context);
