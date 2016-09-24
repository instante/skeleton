<?php
use Instante\Deployment\ProjectInitializer;

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
        foreach ($projectInitializer->getErrors() as $error) {
            echo $error . "\n";
        }
        echo 'There were ' . ($numErrors > 1 ? $numErrors . ' errors' : $numErrors . ' error')
            . '. Install file was NOT deleted.';
    } else {
        unlink(__FILE__);
        header('location: ' . $_SERVER['REQUEST_URI']);
    }
    die;
}

require_once __DIR__ . '/helpers/latte.php';
latte('init');
