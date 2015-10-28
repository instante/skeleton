<?php

namespace Instante\Tests;

use Nette\DI\Container;
use Tester\TestCase;

abstract class ContainerTest extends TestCase
{
    /** @var Container */
    protected $context;

    public function __construct(Container $context)
    {
        $this->context = $context;
    }
}
