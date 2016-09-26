<?php

namespace Instante\Tests;

use Instante\Tests\Doctrine\DoctrineTestCase;
use Tester\Assert;

$context = require '../integration/bootstrap.php';

/**
 * A simple check of functionality of integration tests bootstrap
 */
class TestEnvironmentTest extends DoctrineTestCase
{
    public function testNothing()
    {
        Assert::true(TRUE);
    }
}

TestEnvironmentTest::createFromContainer($context)->run();
