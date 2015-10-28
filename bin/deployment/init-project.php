<?php
use Instante\Deployment\ProjectInitializer;

ini_set('display_errors', 'on');
error_reporting(E_ALL);

$baseDir = realpath(__DIR__ . '/../..');
require_once $baseDir . '/libs/composer/autoload.php';
require_once __DIR__ . '/ProjectInitializer.php';

$projectInitializer = new ProjectInitializer($baseDir);
if ($projectInitializer->checkProjectInitialized()) {
    header('content-type:text/plain');
    die("Project is initialized.\n\nRe-initialize disabled for security reasons.\n"
        . 'Delete the app/config/environment file to re-initialize.');
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
?>
<!DOCTYPE html>
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

    <?php


    if (!empty($_POST['install'])) {


        require_once __DIR__ . '/../libs/composer/nette/neon/src/neon.php';
        require_once __DIR__ . '/../libs/composer/nette/utils/src/Utils/Json.php';


        $composerJsonFilePath = __DIR__ . '/../composer.json';
        $composerJson = file_get_contents($composerJsonFilePath);
        $composerJsonConfig = \Nette\Utils\Json::decode($composerJson, \Nette\Utils\Json::FORCE_ARRAY);
        $composerJsonConfig['name'] = $_POST['project_name'];
        $composerJsonConfig['description'] = $_POST['project_description'];
        $composerJsonConfig['license'] = $_POST['project_license'];
        $composerJsonConfig['version'] = $_POST['project_version'];
        $composerJsonConfig['authors'] = [
            [
                'name' => $_POST['author_name'],
                'email' => $_POST['author_email'],
            ],
        ];
        $composerJson = \Nette\Utils\Json::encode($composerJsonConfig, \Nette\Utils\Json::PRETTY);
        if (FALSE === file_put_contents($composerJsonFilePath, $composerJson)) {
            $numErrors++;
            print_error_message('Composer.json config could not be written at [' . $composerJsonFilePath . ']');
        } else {
            print_ok_message('Composer.json was updated');
        }


        $bowerJsonFilePath = __DIR__ . '/../frontend/bower.json';
        $bowerJson = file_get_contents($bowerJsonFilePath);
        $bowerJsonConfig = \Nette\Utils\Json::decode($bowerJson, \Nette\Utils\Json::FORCE_ARRAY);
        $bowerJsonConfig['name'] = $_POST['project_name'];
        $bowerJsonConfig['description'] = $_POST['project_description'];
        $bowerJsonConfig['license'] = $_POST['project_license'];
        $bowerJsonConfig['version'] = $_POST['project_version'];
        $bowerJsonConfig['authors'] = [
            $_POST['author_name'],
        ];
        $bowerJson = \Nette\Utils\Json::encode($bowerJsonConfig, \Nette\Utils\Json::PRETTY);
        if (FALSE === file_put_contents($bowerJsonFilePath, $bowerJson)) {
            $numErrors++;
            print_error_message('Bower.json config could not be written at [' . $bowerJsonFilePath . ']');
        } else {
            print_ok_message('Bower.json was updated');
        }


        $packageJsonFilePath = __DIR__ . '/../frontend/package.json';
        $packageJson = file_get_contents($packageJsonFilePath);
        $packageJsonConfig = \Nette\Utils\Json::decode($packageJson, \Nette\Utils\Json::FORCE_ARRAY);
        $packageJsonConfig['name'] = $_POST['project_name'];
        $packageJsonConfig['description'] = $_POST['project_description'];
        $packageJsonConfig['license'] = $_POST['project_license'];
        $packageJsonConfig['version'] = $_POST['project_version'];
        $packageJsonConfig['author'] = $_POST['author_name'];
        $packageJson = \Nette\Utils\Json::encode($packageJsonConfig, \Nette\Utils\Json::PRETTY);
        file_put_contents($packageJsonFilePath, $packageJson);
        if (FALSE === file_put_contents($bowerJsonFilePath, $bowerJson)) {
            $numErrors++;
            print_error_message('Package.json config could not be written at [' . $bowerJsonFilePath . ']');
        } else {
            print_ok_message('Package.json was updated');
        }
    }
    ?>

    <p>Fill these values to instantly get your project ready! These values will be written into nette, composer, node
        and bower configurations respectively.</p>

    <form method="POST" class="form-horizontal">
        <fieldset>
            <legend>Project:</legend>
            <div class="form-group">
                <label class="col-sm-4 control-label">Name:</label>

                <div class="col-sm-8"><input name="project_name" class="form-control"/></div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label">Description:</label>

                <div class="col-sm-8"><input name="project_description" class="form-control"/></div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label">License:</label>

                <div class="col-sm-8"><input name="project_license" class="form-control" value="proprietary"/></div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label">Version:</label>

                <div class="col-sm-8"><input name="project_version" class="form-control" value="0.1.0"/></div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Author:</legend>
            <div class="form-group">
                <label class="col-sm-4 control-label">Name:</label>

                <div class="col-sm-8"><input name="author_name" class="form-control"/></div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label">Email:</label>

                <div class="col-sm-8"><input name="author_email" class="form-control"/></div>
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
