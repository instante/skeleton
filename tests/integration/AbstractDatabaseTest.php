<?php

namespace Instante\Tests;

use Doctrine\DBAL\Migrations\Migration;
use Doctrine\ORM\EntityManager;
use Nette\DI\Container;

abstract class AbstractDatabaseTest extends \Tester\TestCase
{
    /** @var EntityManager */
    protected $em;

    /** @var Container */
    protected $container;

    /**
     * TestBase constructor.
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        \Tester\Environment::lock('db', $this->container->getParameters()['tempDir']);
    }


    protected function setUp()
    {
        $this->em = $this->container->getByType('Kdyby\Doctrine\EntityManager');
        $this->em->clear();

        $this->clearDB();

        $migrationConfig = $this->container->getByType('Doctrine\DBAL\Migrations\Configuration\Configuration');

        $migration = new Migration($migrationConfig);
        try {
            $migration->migrate();
        } catch (\Doctrine\DBAL\Migrations\MigrationException $ex) {
            if ($ex->getCode() !== 4) {
                // no migrations found; this should not break tests in early stages of development,
                // the tests will fail either when they start to need a model
                throw $ex;
            }
        }
    }

    protected function clearDB()
    {
        $connection = $this->em->getConnection();
        $tables = $connection->getSchemaManager()->listTableNames();
        $connection->prepare('SET FOREIGN_KEY_CHECKS = 0')->execute();
        foreach ($tables as $table) {
            if ($table !== 'db_version') {
                $connection->prepare('TRUNCATE TABLE ' . $table)->execute();
            }
        }
        $connection->prepare('SET FOREIGN_KEY_CHECKS = 1')->execute();
    }
}
