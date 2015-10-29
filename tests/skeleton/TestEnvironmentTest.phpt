<?php

namespace Instante\Tests;

use Tester\Assert;

$context = require '../integration/bootstrap.php';

/**
 * A simple check of functionality of integration tests bootstrap
 */
class TestEnvironmentTest extends DatabaseTest
{
    public function testNothing()
    {
        Assert::true(TRUE);
    }
}

(new TestEnvironmentTest($context))->run();
