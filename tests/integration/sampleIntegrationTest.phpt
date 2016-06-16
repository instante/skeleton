<?php

$context = require __DIR__ . '/bootstrap.php';
use Tester\Assert;

Assert::type(\Nette\DI\Container::class, $context);
