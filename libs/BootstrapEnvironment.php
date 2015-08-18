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

    /**
     * @param string config dir
     * @return string
     */
    private static function detectEnvironment($configDir)
    {
        if (file_exists($f = $configDir . "/environment")) {
            return trim(file_get_contents($f));
        } else {
            die("The application is not configured to run in this environment - no environment file found.");
        }
    }

    /**
     * Detects if debug mode should be enabled.
     *
     * To enable debug mode from url query, use ?debugMode=1; to disable, use ?debugMode=0.
     * Configuration is then stored to cookie debugMode=yes|no
     *
     * @param array developer IPs: To allow debug mode, you MUST be on one of these IPs
     * @param string environment identifier: enumeration of self::ENV_*
     * @return bool true if debug mode was set
     */
    private static function detectDebugMode($developerIps, $environment = NULL)
    {
        $debugMode = FALSE;
        $debugModeAllowed = in_array(array_key_exists('REMOTE_ADDR', $_SERVER) ? $_SERVER['REMOTE_ADDR'] :
            php_uname('n'), $developerIps, TRUE);
        if ($debugModeAllowed) {
            //just a complicated but proof method to get cookie parameter
            $useFilter = (!in_array(ini_get('filter.default'), ['', 'unsafe_raw']) || ini_get('filter.default_flags'));
            $cookies = $useFilter ? filter_input_array(INPUT_COOKIE, FILTER_UNSAFE_RAW) :
                (empty($_COOKIE) ? [] : $_COOKIE);
            // (C) Nette Framework

            $isCookieSet = array_key_exists(self::IS_DEBUGGING_KEY, $cookies);
            $isQuerySet = array_key_exists(self::IS_DEBUGGING_KEY, $_GET);
            $debugMode = ($isCookieSet && $cookies[self::IS_DEBUGGING_KEY] == 'yes')
                || (!$isCookieSet
                    && $environment == self::ENV_DEVELOPMENT);
            if ($isQuerySet) {
                $debugMode = (bool)$_GET[self::IS_DEBUGGING_KEY];
            }

            $cookieExpiration = new \DateTime('+1 day');
            setcookie(self::IS_DEBUGGING_KEY, $debugMode ? 'yes' :
                'no', $cookieExpiration->format('U'), '/', NULL, FALSE, TRUE);
        }

        return $debugMode;
    }
}
