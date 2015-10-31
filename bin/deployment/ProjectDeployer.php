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
    private $environment;
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

    /** @return array */
    public function getErrors()
    {
        return $this->errors;
    }

    public function deploy()
    {
        if ($this->checkProjectConfigured()) {
            throw new InvalidStateException('Cannot initialize already configured project');
        }
        $this->configureLocalNeon();
        $this->configureEnvironment();
    }

    /**
     * @param string $dbHost
     * @param string $dbUser
     * @param string $dbPassword
     * @param string $dbName
     * @return $this
     */
    public function setDatabaseCredentials($dbHost, $dbUser, $dbPassword, $dbName)
    {
        $this->dbHost = $dbHost;
        $this->dbUser = $dbUser;
        $this->dbPassword = $dbPassword;
        $this->dbName = $dbName;
        return $this;
    }

    /**
     * @param string$environment
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setEnvironment($environment)
    {
        if (!in_array($environment, $envs = [Bootstrapper::ENV_DEVELOPMENT, Bootstrapper::ENV_STAGE, Bootstrapper::ENV_PRODUCTION])) {
            throw new InvalidArgumentException('Environment must be one of ' . implode(',', $envs));
        }
        $this->environment = $environment;
        return $this;
    }

    public function deployFromConsole()
    {
        $stdin = fopen("php://stdin","r");

        echo "Select local environment ( [d]evelopment, [s]tage, [p]roduction ):\n";
        while (!in_array($c = strtolower(fgetc($stdin)), ['d','s','p'])) {
            echo "Press D, S or P";
        }

        if (!feof($stdin)) {
            $str = fgets($stdin);
            if (trim($str) === '') {
                $str = fgets($stdin);
            }
        }

        echo "Local database setup\n"
           . "--------------------";
        echo "User name > ";
        $this->dbUser = isset($str) ? $str : trim(fgets($stdin));

        echo "Password > ";
        $this->dbPassword = trim(fgets($stdin));

        echo "Database (schema) name > ";
        $this->dbName = trim(fgets($stdin));

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
        $localNeonPath = $this->dir . '/app/config/local.neon';
        if (file_put_contents($localNeonPath, Neon::encode($config, Neon::BLOCK)) === FALSE) {
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
}
