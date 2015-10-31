<?php
use Instante\Deployment\ProjectInitializer;

ini_set('display_errors', 'on');
error_reporting(E_ALL);

$baseDir = realpath(__DIR__ . '/../..');
require_once $baseDir . '/vendor/autoload.php';
require_once __DIR__ . '/ProjectInitializer.php';

$projectInitializer = new ProjectInitializer($baseDir);
if ($projectInitializer->checkProjectConfigured()) {
    header('content-type:text/plain');
    die("Project is initialized.\n\nRe-initialize disabled for security reasons.\n"
        . 'Delete the app/config/environment file to re-initialize.');
}

if (php_sapi_name() === 'cli') {
    $projectInitializer->initializeFromConsole();
    die;
}


if (!empty($_POST['install'])) {
    $projectInitializer
        ->setWebmasterEmail($_POST['webmasterEmail'])
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

    <h1 class="text-center">Instante project initialization</h1>

    <p>Fill these values to instantly get your project ready! These values will be written into nette, composer, node
        and bower configurations respectively.</p>

    <form method="POST" class="form-horizontal">
        <fieldset>
            <legend>Project:</legend>
            <div class="form-group">
                <label class="col-sm-4 control-label" for="project_name">Name:</label>

                <div class="col-sm-8"><input id="project_name" name="project_name" class="form-control" /></div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label" for="project_description">Description:</label>

                <div class="col-sm-8"><input id="project_description" name="project_description" class="form-control" /></div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label" for="project_license">License:</label>

                <div class="col-sm-8"><input id="project_license" name="project_license" class="form-control" value="proprietary" /></div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label" for="project_version">Version:</label>

                <div class="col-sm-8"><input id="project_version" name="project_version" class="form-control" value="0.1.0" /></div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label" for="webmaster_email">Webmaster email:</label>

                <div class="col-sm-8"><input id="webmaster_email" name="webmaster_email" class="form-control" /></div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Author:</legend>
            <div class="form-group">
                <label class="col-sm-4 control-label" for="author_name">Name:</label>

                <div class="col-sm-8"><input id="author_name" name="author_name" class="form-control" /></div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label" for="author_email">Email:</label>

                <div class="col-sm-8"><input id="author_email" name="author_email" class="form-control" /></div>
            </div>
        </fieldset>

        <div class="form-group">
            <div class="col-xs-12 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4">
                <input name="install" type="submit" value="Install" class="form-control btn btn-success"/>
            </div>
        </div>

    </form>

</div>

</body>
</html>
