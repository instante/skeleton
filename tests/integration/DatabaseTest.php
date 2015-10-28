<?php

namespace Instante\Tests;

use Instante\Tests\Utils\DatabaseTester;
use Kdyby\Doctrine\EntityManager;
use Nette\DI\Container;

abstract class DatabaseTest extends ContainerTest
{
    /** @var DatabaseTest */
    protected $databaseTest;

    /** @var EntityManager */
    protected $em;

    /** @var bool */
    private $prepared = FALSE;

    public function __construct(Container $context)
    {
        parent::__construct($context);
        $this->databaseTest = new DatabaseTester($context);
        $this->em = $context->getByType(EntityManager::class);
    }


    protected function setUp()
    {
        parent::setUp();
        if (!$this->prepared) {
            $this->databaseTest->prepareDatabaseTest();
            $this->prepared = TRUE;
        } else {
            $this->databaseTest->clearDatabase();
        }
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->databaseTest->clearDatabase();
    }


}
