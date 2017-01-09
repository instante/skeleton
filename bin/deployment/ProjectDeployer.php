<?php
namespace Instante\Deployment;


use Instante\Bootstrap\Bootstrapper;
use JakubOnderka\PhpParallelLint\InvalidArgumentException;
use Nette\InvalidStateException;
use Nette\Neon\Neon;

class ProjectDeployer
{
    /** @var string */
    private $dir;

    /** @var string */
    private $dbHost;
    /** @var string */
    private $dbUser;
    /** @var string */
    private $dbPassword;
    /** @var string */
    private $dbName;
    /** @var string */
    private $dbTestName;
    /** @var string */
    private $environment;
    /** @var bool */
    private $secureRoutes;
    /** @var array */
    private $errors = [];

    /**
     * ProjectInitializer constructor.
     * @param string $dir
     */
    public function __construct($dir) { $this->dir = $dir; }

    public function checkProjectConfigured()
    {
        return file_exists($this->dir . '/app/config/environment');
    }

    public function checkProjectInitialized()
    {
        return file_exists($this->dir . '/www/index.uninitialized.php');
    }

    public function checkDatabaseExists($dbName)
    {
        $conn = new \mysqli($this->dbHost, $this->dbUser, $this->dbPassword);
        if ($conn->connect_error) {
            return false;
        }
        else {
            $sql="CREATE DATABASE IF NOT EXISTS `{$dbName}`";
            if ($conn->query($sql) === TRUE) {
                return true;
            } else {
                return false;
            }

        }
    }

    /** @return array */
    public function getErrors()
    {
        return $this->errors;
    }

    public function deploy()
    {
        if ($this->checkProjectConfigured()) {
            throw new InvalidStateException("Cannot initialize already configured project\n");
        }

        if(!$this->checkDatabaseExists($this->dbName)){
            $this->errors[] = 'Database doesn\'t exists and could\'t be created automatically with given credentials.';
            return;

        }

        if ($this->dbTestName)
        {
            if(!$this->checkDatabaseExists($this->dbTestName)){
                $this->errors[] = 'Test database doesn\'t exists and could\'t be created automatically with given credentials.';
                return;
            }
        }

        $this->configureLocalNeon();
        $this->configureEnvironment();
        $out = "";
        if ($this->isWindows()) {
            // todo: create migration script for windows
            $out = `php {$this->dir}/www/index.php orm:generate-proxies`;
            $out .= "\n\n" . `php {$this->dir}/www/index.php migrations:migrate`;
        } else {
            $out .= "\n\n" . `{$this->dir}/bin/migrate.sh`;
        }
        if (php_sapi_name() === 'cli') {
            echo $out;
        }
    }

    /**
     * @param string $dbHost
     * @param string $dbUser
     * @param string $dbPassword
     * @param string $dbName
     * @param $dbTestName
     * @return $this
     */
    public function setDatabaseCredentials($dbHost, $dbUser, $dbPassword, $dbName, $dbTestName)
    {
        $this->dbHost = $dbHost;
        $this->dbUser = $dbUser;
        $this->dbPassword = $dbPassword;
        $this->dbName = $dbName;
        $this->dbTestName = $dbTestName;
        return $this;
    }

    /**
     * @param string $environment
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setEnvironment($environment)
    {
        if (!in_array($environment, $envs = [
            Bootstrapper::ENV_DEVELOPMENT,
            Bootstrapper::ENV_STAGE,
            Bootstrapper::ENV_PRODUCTION,
        ])
        ) {
            throw new InvalidArgumentException('Environment must be one of ' . implode(',', $envs));
        }
        $this->environment = $environment;
        return $this;
    }

    /**
     * @param boolean $secureRoutes
     * @return $this
     */
    public function setSecureRoutes($secureRoutes)
    {
        $this->secureRoutes = (bool)$secureRoutes;
        return $this;
    }

    public function deployFromConsole()
    {
        $stdin = fopen("php://stdin", "r");

        echo "\n\n\nSelect local environment ( [d]evelopment | [s]tage | [p]roduction ):\n";
        while (!in_array($c = substr(strtolower(trim(fgets($stdin))), 0, 1), ['d', 's', 'p'])) {
            echo "Type D, S or P and press ENTER\n";
        }

        switch ($c) {
            case 'd':
                $this->environment = Bootstrapper::ENV_DEVELOPMENT;
                break;
            case 's':
                $this->environment = Bootstrapper::ENV_STAGE;
                break;
            case 'p':
                $this->environment = Bootstrapper::ENV_PRODUCTION;
                break;
        }

        echo "Use secure routes (HTTPS)? ( [y]es | [n]o ):\n";
        while (!in_array($c = substr(strtolower(trim(fgets($stdin))), 0, 1), ['y', 'n'])) {
            echo "Type Y or N and press ENTER\n";
        }

        $this->secureRoutes = $c === 'y';

        echo "Local database setup\n"
            . "--------------------\n"
            . "User name > ";
        $this->dbUser = isset($str) ? $str : trim(fgets($stdin));
        if ($this->isWindows()) {
            echo "WARNING: Password input is not masked in Windows command prompt\n"
                . "Password > ";
        } else {
            echo "Password > \033[30;40m";
        }

        $this->dbPassword = trim(fgets($stdin));
        if (!$this->isWindows()) {
            echo "\033[0m";
        }

        echo "Database (schema) name > ";
        $this->dbName = trim(fgets($stdin));

        echo "Database name for tests (optional, leave blank if not needed) > ";
        $this->dbTestName = trim(fgets($stdin));

        echo "Server IP (leave blank for 127.0.0.1; using IP instead of DNS recommended if possible) > ";
        $host = trim(fgets($stdin));
        $this->dbHost = $host === '' ? '127.0.0.1' : $host;

        $this->deploy();

        foreach ($this->errors as $error) {
            echo $error . "\n";
        }

    }

    private function configureLocalNeon()
    {

        $config = Neon::decode(file_get_contents($this->dir . '/app/config/local.neon.example'));
        $config['parameters']['database']['host'] = $this->dbHost;
        $config['parameters']['database']['user'] = $this->dbUser;
        $config['parameters']['database']['password'] = $this->dbPassword;
        $config['parameters']['database']['dbname'] = $this->dbName;
        $config['parameters']['database']['dbname_test'] = $this->dbTestName;
        $config['parameters']['routes']['secure'] = $this->secureRoutes;
        $localNeonPath = $this->dir . '/app/config/local.neon';
        $localNeonHead
            = <<<EOT
#
# Local machine configuration.
# SECURITY WARNING: it is CRITICAL that this file & directory
# are NOT accessible directly via a web browser!
# This file should also NEVER be versioned.
#


EOT;

        if (file_put_contents($localNeonPath, $localNeonHead
                . str_replace("\t", '    ', Neon::encode($config, Neon::BLOCK))) === FALSE
        ) {
            $this->errors[] = 'Local config could not be written at [' . $localNeonPath . ']';
        }
    }

    private function configureEnvironment()
    {
        $environmentFilePath = $this->dir . '/app/config/environment';
        if (file_put_contents($environmentFilePath, $this->environment) === FALSE) {
            $this->errors[] = 'Environment file could not be written at [' . $environmentFilePath . ']';
        }
    }

    private function isWindows()
    {
        return substr(strtoupper(PHP_OS), 0, 3) === 'WIN';
    }
}
