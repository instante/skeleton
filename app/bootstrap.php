<?php
$rootDir = __DIR__ . '/..';
$appDir = __DIR__;
$wwwDir = $rootDir . '/www';
$tempDir = $rootDir . '/temp';
$paths = [ //additional paths
    'root' => $rootDir,
    'libs' => $rootDir . '/libs',
    'log' => $rootDir . '/log',
];

require_once $paths['libs'] . '/BootstrapEnvironment.php';

$configDir = $appDir . '/config';
$environment = Instante\BootstrapEnvironment::configure($configDir);


// Load autoloader for composer dependencies
require_once $paths['libs'] . '/composer/autoload.php';


$configurator = new Nette\Configurator;


$configurator->addParameters([
    'paths' => $paths,
]);

// Enable Nette Debugger for error visualisation & logging
$configurator->setDebugMode($environment->isDebugMode());
$configurator->enableDebugger($paths['log']);

// Specify folder for cache
$configurator->setTempDirectory($tempDir);

// Enable RobotLoader - autoloader for project files and non-composer libraries
$configurator->createRobotLoader()
    ->addDirectory($appDir)
    ->addDirectory($paths['libs'] . '/vendor')
    ->register();


// Create Dependency Injection container from config.neon file
$configurator->addConfig($configDir . '/default.neon');

// debug mode dependent config
if ($environment->isDebugMode()) {
    $configurator->addConfig($configDir . '/debug.neon', $configurator::NONE); // sections not used
}

// environment dependent config
if (file_exists($envConfig = $configDir . '/env.' . $environment->getEnvironment() . '.neon')) {
    $configurator->addConfig($envConfig, $configurator::NONE); // sections not used
}

// local machine config
if (file_exists($configDir . '/local.neon')) {
    $configurator->addConfig($configDir . '/local.neon', $configurator::NONE); // sections not used
}

$container = $configurator->createContainer();

return $container;
