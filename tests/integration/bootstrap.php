<?php

/**
 * init test dependent on nette container
 */

use Nette\Configurator;

require_once __DIR__ . '/../unit/bootstrap.php';
$rootDir = __DIR__ . '/../../';

$configDir = $rootDir . 'app/config';

$paths = [ //additional paths
    'root' => $rootDir,
    'libs' => $rootDir . '/libs',
    'log' => $rootDir . '/log',
];


$configurator = new Configurator;
$configurator->addParameters(['appDir' => __DIR__ . '/../../app']);
$configurator->setTempDirectory(TEMP_DIR);


$configurator->addConfig("$configDir/default.neon");
if (file_exists("$configDir/local.neon")) {
    $configurator->addConfig("$configDir/local.neon", $configurator::NONE); // sections not used
}
$configurator->addConfig(['doctrine' => ['dbname' => '%database.dbname_test%']]);

$configurator->addParameters([
    'paths' => $paths,
]);

return $configurator->createContainer();
