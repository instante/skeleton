<?php

namespace Instante;

/**
 * Nette debug mode and environment configurator
 */
class BootstrapEnvironment
{

    const ENV_DEVELOPMENT = 'development';
    const ENV_STAGE = 'stage';
    const ENV_PRODUCTION = 'production';
    const IS_DEBUGGING_KEY = 'debugMode';

    /** @var string */
    private $environment;

    /** @var bool */
    private $debugMode;

    /**
     * @param string $environment
     * @param bool $debugMode
     */
    protected function __construct($environment, $debugMode)
    {
        $this->environment = $environment;
        $this->debugMode = $debugMode;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @return boolean
     */
    public function isDebugMode()
    {
        return $this->debugMode;
    }

    /**
     * @param string path to config
     * @param array IP adresses to enable debugging
     * @return self
     */
    public static function configure($configDir, $developersIps = ['127.0.0.1', '::1'])
    {
        $environment = self::detectEnvironment($configDir);
        $debugMode = self::detectDebugMode($developersIps, $environment);

        return new self($environment, $debugMode);
    }
}
