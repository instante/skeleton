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

    <div class="col-sm-4"></div>
    <div class="col-sm-4">

        <h1 class="text-center">Instante installation</h1>

<?php
        /** @param $message */
        function print_ok_message($message) {
            echo '<div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span> ' . $message . '</div>';
        }

        /** @param $message */
        function print_error_message($message) {
            echo '<div class="alert alert-danger"><span class="glyphicon glyphicon-exclamation-sign"></span> <span>' . $message . '</span></div>';
        }

        if (!empty($_POST['install'])) {
            // TODO add file exists check
            // TODO functionality to add to config instead of replace

            ini_set('display_errors','on');
            error_reporting(E_ALL);

            if (!file_exists(dirname(__FILE__) . '/../composer.lock')) {
                print_error_message('Composer was not installed, you need to run "composer install" first');
                exit;
            }

            require_once dirname(__FILE__) . '/../libs/composer/nette/neon/src/neon.php';
            require_once dirname(__FILE__) . '/../libs/composer/nette/utils/src/Utils/Json.php';

            $errors = 0;
            $messages = [];

            $configFolderPath = dirname(__FILE__) . '/../app/config';
            $defaultNeonFilePath = $configFolderPath . '/default.neon';
            $defaultNeon = file_get_contents($defaultNeonFilePath);
            $defaultNeonConfig = \Nette\Neon\Neon::decode($defaultNeon);
            $defaultNeonConfig['parameters']['webmasterEmail'] = $_POST['webmaster_email'];
            $defaultNeon = '#
# SECURITY WARNING: it is CRITICAL that this file & directory are NOT accessible directly via a web browser!
#
# If you don\'t protect this directory from direct web access, anybody will be able to see your passwords.
# http://nette.org/security-warning
#
'. \Nette\Neon\Neon::encode($defaultNeonConfig, \Nette\Neon\Neon::BLOCK);
            file_put_contents($defaultNeonFilePath, $defaultNeon);

            $logFolderPath = dirname(__FILE__) . '/../log';
            if (!is_dir($logFolderPath) && !@mkdir($logFolderPath)) {
                $errors++;
                print_error_message('Log folder could not be created at [' . $logFolderPath . ']');
            } else {
                print_ok_message('Log folder created at [' . $logFolderPath . ']');
            }


            $cacheFolderPath = dirname(__FILE__) . '/../temp/cache';
            if (!is_dir($cacheFolderPath) && !@mkdir($cacheFolderPath, 0777, TRUE)) {
                $errors++;
                print_error_message('Cache folder could not be created at [' . $cacheFolderPath . ']');
            } else {
                print_ok_message('Cache folder created at [' . $cacheFolderPath . ']');
            }


            $environmentFilePath = $configFolderPath . '/environment';
            if (FALSE === file_put_contents($environmentFilePath, $_POST['environment'])) {
                $errors++;
                print_error_message('Environment file could not be written at [' . $environmentFilePath . ']');
            } else {
                print_ok_message('Environment file was written');
            }


            $developmentDatabaseConfig = [
                'parameters' => [
                    'database' => [
                        'driver' => 'pdo_mysql',
                        'host' => $_POST['development_database_host'],
                        'dbname' => $_POST['development_database_name'],
                        'user' => $_POST['development_database_user'],
                        'password' => $_POST['development_database_password'],
                        'charset' => 'utf8',
                        'collation' => 'utf8_czech_ci',
                    ]
                ],
            ];
            $developmentEnvironmentConfigFilePath = $configFolderPath . '/env.development.neon';
            $developmentEnvironmentNeon = '#
# Configuration applied only in development environment
#
' . \Nette\Neon\Neon::encode($developmentDatabaseConfig, \Nette\Neon\Neon::BLOCK);
            if (FALSE === file_put_contents($developmentEnvironmentConfigFilePath, $developmentEnvironmentNeon)) {
                $errors++;
                print_error_message('Development environment file could not be written at [' . $developmentEnvironmentConfigFilePath . ']');
            } else {
                print_ok_message('Development environment file was written');
            }


            $databaseNeonFilePath = $configFolderPath . '/database.neon';
            $databaseNeon = file_get_contents($databaseNeonFilePath);
            $databaseNeonConfig = \Nette\Neon\Neon::decode($databaseNeon);
            $databaseConfig = [
                'driver' => 'pdo_mysql',
                'host' => $_POST['production_database_host'],
                'dbname' => $_POST['production_database_name'],
                'user' => $_POST['production_database_user'],
                'password' => $_POST['production_database_password'],
                'charset' => 'utf8',
                'collation' => 'utf8_czech_ci',
            ];
            $databaseNeonConfig['parameters']['database'] = $databaseConfig;
            $databaseNeonConfig = \Nette\Neon\Neon::encode($databaseNeonConfig, \Nette\Neon\Neon::BLOCK);
            if (FALSE === file_put_contents($databaseNeonFilePath, $databaseNeonConfig)) {
                $errors++;
                print_error_message('Database config could not be written at [' . $databaseNeonFilePath . ']');
            } else {
                print_ok_message('Database config was written');
            }


            $composerJsonFilePath = dirname(__FILE__) . '/../composer.json';
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
                ]
            ];
            $composerJson = \Nette\Utils\Json::encode($composerJsonConfig, \Nette\Utils\Json::PRETTY);
            if (FALSE === file_put_contents($composerJsonFilePath, $composerJson)) {
                $errors++;
                print_error_message('Composer.json config could not be written at [' . $composerJsonFilePath . ']');
            } else {
                print_ok_message('Composer.json was updated');
            }


            $bowerJsonFilePath = dirname(__FILE__) . '/../frontend/bower.json';
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
                $errors++;
                print_error_message('Bower.json config could not be written at [' . $bowerJsonFilePath . ']');
            } else {
                print_ok_message('Bower.json was updated');
            }


            $packageJsonFilePath = dirname(__FILE__) . '/../frontend/package.json';
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
                $errors++;
                print_error_message('Package.json config could not be written at [' . $bowerJsonFilePath . ']');
            } else {
                print_ok_message('Package.json was updated');
            }


            $instanteCodingStandardSrc = dirname(__FILE__) . '/../codingStandards/Instante';
            $instanteCodingStandardInCodeSinffer = dirname(__FILE__) . '/../libs/composer/squizlabs/php_codesniffer/CodeSniffer/Standards/Instante';
            if (!is_link($instanteCodingStandardInCodeSinffer) && FALSE === symlink($instanteCodingStandardSrc, $instanteCodingStandardInCodeSinffer)) {
                $errors++;
                print_error_message('Could not create symlink for Instante coding standards at [' . $instanteCodingStandardInCodeSinffer . ']');
            } else {
                print_ok_message('Instante coding standards installed');
            }


            // delete this file
            if ($errors) {
                echo '<div class="alert alert-danger"><h1>There were ' . ($errors > 1 ? $errors . ' errors' : $errors . ' error') . '.</h1> Install file was NOT deleted.</div>';
            } else {
                // TODO check if install.php was deleted
//                unlink(__FILE__);
                echo '<div class="alert alert-success text-center"><h1>Installation successful!</h1> Install file was deleted. Click <a href="index.php">here</a> to redirect to index.</div>';
                exit;
            }
        }
?>

        <p>Fill these values to instantly get your project ready! These values will be written into nette, composer, node and bower configurations respectively.</p>

        <form method="POST" class="form-horizontal">
            <fieldset>
                <legend>Project:</legend>
                <div class="form-group">
                    <label class="col-sm-4 control-label">Name:</label>
                    <div class="col-sm-8"><input name="project_name" class="form-control" /></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label">Description:</label>
                    <div class="col-sm-8"><input name="project_description" class="form-control" /></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label">License:</label>
                    <div class="col-sm-8"><input name="project_license" class="form-control" value="proprietary"  /></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label">Version:</label>
                    <div class="col-sm-8"><input name="project_version" class="form-control" value="0.1.0"  /></div>
                </div>
            </fieldset>

            <fieldset>
                <legend>Author:</legend>
                <div class="form-group">
                    <label class="col-sm-4 control-label">Name:</label>
                    <div class="col-sm-8"><input name="author_name" class="form-control" /></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label">Email:</label>
                    <div class="col-sm-8"><input name="author_email" class="form-control" /></div>
                </div>
            </fieldset>

            <fieldset>
                <legend>Development:</legend>
                <div class="form-group">
                    <label class="col-sm-4 control-label">Default environment:</label>
                    <div class="col-sm-8">
                        <select name="environment" class="form-control">
                            <option name="development">development</option>
                            <option name="production">production</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label">Webmaster email:</label>
                    <div class="col-sm-8"><input name="webmaster_email" class="form-control" /></div>
                </div>
            </fieldset>

            <fieldset>
                <legend>Development database:</legend>
                <div class="form-group">
                    <label class="col-sm-4 control-label">Host:</label>
                    <div class="col-sm-8"><input name="development_database_host" class="form-control" /></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label">Database name:</label>
                    <div class="col-sm-8"><input name="development_database_name" class="form-control" /></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label">User:</label>
                    <div class="col-sm-8"><input name="development_database_user" class="form-control" /></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label">Password:</label>
                    <div class="col-sm-8"><input type="password" name="development_database_password" class="form-control" /></div>
                </div>
            </fieldset>

            <fieldset>
                <legend>Production database:</legend>
                <div class="form-group">
                    <label class="col-sm-4 control-label">Host:</label>
                    <div class="col-sm-8"><input name="production_database_host" class="form-control" /></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label">Database name:</label>
                    <div class="col-sm-8"><input name="production_database_name" class="form-control" /></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label">User:</label>
                    <div class="col-sm-8"><input name="production_database_user" class="form-control" /></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label">Password:</label>
                    <div class="col-sm-8"><input type="password" name="production_database_password" class="form-control" /></div>
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
