<?php

namespace Instante\Tests;

use Tester\Assert;

$context = require 'bootstrap.php';

/**
 * A simple check of functionality of integration tests bootstrap
 */
class BootstrapTest extends DatabaseTest
{
    public function testNothing()
    {
        Assert::true(TRUE);
    }
}

(new BootstrapTest($context))->run();
