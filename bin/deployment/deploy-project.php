<?php
use Instante\Deployment\ProjectDeployer;

ini_set('display_errors', 'on');
error_reporting(E_ALL);


$baseDir = realpath(__DIR__ . '/../..');
$autoloaderPath = $baseDir . '/vendor/autoload.php';

if (!file_exists($autoloaderPath)) {
    header('content-type:text/plain');
    die('Composer was not installed, you need to run "composer install" first');
}

require_once $autoloaderPath;
require_once __DIR__ . '/ProjectDeployer.php';

$projectDeployer = new ProjectDeployer($baseDir);
if ($projectDeployer->checkProjectConfigured()) {
    header('content-type:text/plain');
    die("Project is deployed.\n\nRe-deploy disabled for security reasons.\n"
        . 'Delete the app/config/environment file to re-initialize.');
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
        header('location: ' . $_SERVER['REQUEST_URI']);
    }
    die;
}
?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
    <!--[if lt IE 9]>
    <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">

    <h1 class="text-center">Instante installation</h1>

    <p>Fill these values to instantly initialize your local copy! This script will configures local environment, database credentials etc. for you.</p>

    <form method="POST" class="form-horizontal">

        <fieldset>
            <legend>Environment:</legend>
            <div class="form-group">
                <label class="col-sm-4 control-label" for="environment">Set local environment to:</label>
                <div class="col-sm-8">
                    <select id="environment" name="environment" class="form-control">
                        <option name="development">development</option>
                        <option name="stage">stage</option>
                        <option name="production">production</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label" for="secure">Use secure routes (HTTPS):</label>
                <div class="col-sm-8">
                    <input type="checkbox" id="secure" name="secure" class="form-control" />
                </div>
            </div>

        </fieldset>

        <fieldset>
            <legend>Local database:</legend>
            <div class="form-group">
                <label class="col-sm-4 control-label" for="database_host">Host:</label>

                <div class="col-sm-8"><input id="database_host" name="database_host" value="127.0.0.1" class="form-control" /></div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label" for="database_name">Database (schema) name:</label>

                <div class="col-sm-8"><input id="database_name" name="database_name" class="form-control" /></div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label" for="database_test_name">Database name for tests (optional):</label>

                <div class="col-sm-8"><input id="database_test_name" name="database_test_name" class="form-control" /></div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label" for="database_user">User:</label>

                <div class="col-sm-8"><input id="database_user" name="database_user" class="form-control" /></div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label" for="database_password">Password:</label>

                <div class="col-sm-8"><input id="database_password" type="password" name="database_password" class="form-control" /></div>
            </div>
        </fieldset>

        <div class="form-group">
            <div class="col-sm-12">
                <input name="install" type="submit" value="Install" class="form-control btn btn-success" />
            </div>
        </div>

    </form>

</div>

</body>
</html>
