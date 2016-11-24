<?php
use Instante\Deployment\ProjectDeployer;

ini_set('display_errors', 'on');
error_reporting(E_ALL);


$baseDir = realpath(__DIR__ . '/../..');

require_once __DIR__ . '/helpers/composer.php';
loadComposer($baseDir);

require_once __DIR__ . '/helpers/common.php';
require_once __DIR__ . '/ProjectDeployer.php';

$projectDeployer = new ProjectDeployer($baseDir);
if ($projectDeployer->checkProjectInitialized()) {
    header('location:init-project.php');
    die("\nProject is not initialized yet.\n"
        . "Please run init-project.php first.\n\n");
}
if ($projectDeployer->checkProjectConfigured()) {
    header('content-type:text/plain');
    die("\nProject is deployed.\n\nRe-deploy disabled for security reasons.\n"
        . "Delete the app/config/environment file to re-initialize.\n\n");
}

if (php_sapi_name() === 'cli') {
    $projectDeployer->deployFromConsole();
    die;
}


$args = [];
if (!empty($_POST['install'])) {
    $projectDeployer
        ->setDatabaseCredentials($_POST['database_host'], $_POST['database_user'], $_POST['database_password'], $_POST['database_name'], $_POST['database_test_name'])
        ->setEnvironment($_POST['environment'])
        ->setSecureRoutes(isset($_POST['secure']))
        ->deploy();

    $numErrors = count($projectDeployer->getErrors());
    if ($numErrors > 0) {
        $errorMessages = [];
        foreach ($projectDeployer->getErrors() as $error) {
            $errorMessages[] = $error . "\n";
        }
        $args['errorMessages'] = $errorMessages;
    } else {
        redirectToProject();
    }

}

require_once __DIR__ . '/helpers/latte.php';
latte('deploy', $args);

