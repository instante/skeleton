<?php

namespace Instante\Tests\Utils;


use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Migration;
use Doctrine\DBAL\Migrations\MigrationException;
use Kdyby\Doctrine\EntityManager;
use Nette\DI\Container;
use Tester\Environment;

final class DatabaseTester
{
    /** @var Container */
    private $context;

    /** @var EntityManager */
    private $em;

    /**
     * DatabaseTester constructor.
     * @param Container $context
     */
    public function __construct(Container $context)
    {
        $this->context = $context;
        $this->em = $this->context->getByType(EntityManager::class);

    }

    public function prepareDatabaseTest()
    {
        $this->lock();
        $this->em->clear();

        $migrationConfig = $this->context->getByType(Configuration::class);
        /** @var Configuration $migrationConfig */
        $migration = new Migration($migrationConfig);
        try {
            $migration->migrate();
        } catch (MigrationException $ex) {
            if ($ex->getCode() !== 4) {
                // no migrations found; this should not break tests in early stages of development,
                // the tests will fail when they require a model anyway
                throw $ex;
            }
        }
        $this->clearDatabase();
    }

    public function clearDatabase()
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


    private function lock()
    {
        Environment::lock('db', $this->context->getParameters()['tempDir']);
    }
}
