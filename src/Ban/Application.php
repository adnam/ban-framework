<?php

require_once 'Zend/Application.php';
require_once 'Zend/Config.php';
require_once 'Zend/Config/Ini.php';

class Ban_Application extends Zend_Application
{
    
    const CONFIG_CACHE_TIMEOUT = 10; // 10 seconds

    /**
     * Constructor
     *
     * Initialize application. Potentially initializes include_paths, PHP
     * settings, and bootstrap class.
     *
     * @param  string                   $environment
     * @param  string|array|Zend_Config OPTIONAL $options String path to configuration file, or array/Zend_Config of configuration options
     * @throws Zend_Application_Exception When invalid options are provided
     * @return void
     */
    public function __construct($environment, $options = null)
    {
        $cache = null;
        $cachedOptions = false;
        if (null === $options && extension_loaded('apc'))
        {
            require_once 'Zend/Cache/Backend/Apc.php';
            $cache = new Zend_Cache_Backend_Apc();
            $key = 'banconfig_' . APPLICATION_PATH . '_' . $environment;
            $cachedOptions = $cache->load($key);
            if ($cachedOptions !== false) {
                $options = $cachedOptions;
            }
        }

        if (null !== $options) {
            parent::__construct($environment, $options);
        } else {
            $this->_environment = (string) $environment;

            require_once 'Zend/Loader/Autoloader.php';
            $this->_autoloader = Zend_Loader_Autoloader::getInstance();
            
            $apiOptionsPath = APPLICATION_PATH . '/configs/api.ini';
            $localOptionsPath = APPLICATION_PATH . '/configs/local.ini';
            $apiOptionsRealPath = realpath($apiOptionsPath);

            $options = new Zend_Config(array(), true);
            if ($apiOptionsRealPath === false) {
                throw new Exception("Error: config file [$apiOptionsPath] does not exist");
            }
            if (!is_readable($apiOptionsRealPath)) {
                throw new Exception("Error: config file [$apiOptionsPath] is not readable");
            }
            $options = $options->merge(new Zend_Config_Ini($apiOptionsRealPath, $this->_environment));
            
            $localOptionsRealPath = realpath(APPLICATION_PATH . '/configs/local.ini');
            if ($localOptionsRealPath !== false) {
                if (!is_readable($localOptionsRealPath)) {
                    throw new Exception("Error: config file [$localOptionsRealPath] is not readable");
                }
                $options = $options->merge(new Zend_Config_Ini($localOptionsRealPath, $this->_environment));
            }
            $this->setOptions($options->toArray());
        }
        
        if ($cache instanceof Zend_Cache_Backend && $cachedOptions !== false) {
            $cache->save($options, $key, array(), self::CONFIG_CACHE_TIMEOUT);
        }
        
        return $this;
    }
}
