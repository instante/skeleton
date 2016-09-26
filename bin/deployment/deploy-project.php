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


if (!empty($_POST['install'])) {
    $projectDeployer
        ->setDatabaseCredentials($_POST['database_host'], $_POST['database_user'], $_POST['database_password'], $_POST['database_name'], $_POST['database_test_name'])
        ->setEnvironment($_POST['environment'])
        ->setSecureRoutes(isset($_POST['secure']))
        ->deploy();

    header('content-type:text/plain');
    $numErrors = count($projectDeployer->getErrors());
    if ($numErrors > 0) {
        foreach ($projectDeployer->getErrors() as $error) {
            echo $error . "\n";
        }
        echo 'There were ' . ($numErrors > 1 ? $numErrors . ' errors' : $numErrors . ' error');
    } else {
        redirectToProject();
    }
    die;
}

require_once __DIR__ . '/helpers/latte.php';
latte('deploy');
