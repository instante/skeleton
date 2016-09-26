<?php
use Instante\Deployment\ProjectInitializer;

function redirectToProject()
{
    $uri = $_SERVER['REQUEST_URI'];
    $path = preg_replace('~\?.*$~', '', $uri);
    $initPath = '/bin/deployment/init-project.php';
    if (preg_match("~$initPath$~", $path)) {
        $uri = preg_replace("~$initPath$~", '/www/', $path);
    }
    header('location: ' . $uri);
}

ini_set('display_errors', 'on');
error_reporting(E_ALL);

$baseDir = realpath(__DIR__ . '/../..');

require_once __DIR__ . '/helpers/composer.php';
loadComposer($baseDir);

require_once __DIR__ . '/ProjectInitializer.php';

$projectInitializer = new ProjectInitializer($baseDir);
if ($projectInitializer->checkProjectConfigured()) {
    header('content-type:text/plain');
    die("\nProject is initialized.\n\nRe-initialize disabled for security reasons.\n"
        . "Delete the app/config/environment file to re-initialize.\n\n");
}

if (php_sapi_name() === 'cli') {
    $projectInitializer->initializeFromConsole();
    die;
}


if (!empty($_POST['install'])) {
    $projectInitializer
        ->setErrorNotifyEmail($_POST['error_log_email'])
        ->setProjectMeta($_POST['project_name'], $_POST['project_description'], $_POST['project_license'], $_POST['project_version'])
        ->setAuthor($_POST['author_name'], $_POST['author_email'])
        ->initialize();

    header('content-type:text/plain');
    $numErrors = count($projectInitializer->getErrors());
    if ($numErrors > 0) {
        // TODO do it also with shell init
        foreach ($projectInitializer->getErrors() as $error) {
            echo $error . "\n";
        }
        echo 'There were ' . ($numErrors > 1 ? $numErrors . ' errors' : $numErrors . ' error')
            . '. Install file was NOT deleted.';
    } else {
        $projectInitializer->removeItself();
        redirectToProject();
    }
    die;
}

require_once __DIR__ . '/helpers/latte.php';
latte('init');
