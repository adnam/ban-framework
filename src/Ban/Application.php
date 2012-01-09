<?php

require_once 'Zend/Application.php';

class Ban_Application extends Zend_Application
{

    /**
     * Constructor
     *
     * Initialize application. Potentially initializes include_paths, PHP
     * settings, and bootstrap class.
     *
     * @param  string                   $environment
     * @param  string|array|Zend_Config $options String path to configuration file, or array/Zend_Config of configuration options
     * @throws Zend_Application_Exception When invalid options are provided
     * @return void
     */
    public function __construct($environment, $options = null)
    {
        if (null === $options && extension_loaded('apc')) {
            $options = realpath(APPLICATION_PATH . '/configs/api.ini');
            if ($options === false) {
                throw new Exception("Error: config file [$options] does not exist");
            }
            if (!is_readable($options)) {
                throw new Exception("Error: config file [$options] does not exist");
            }
            require_once 'Zend/Cache/Backend/Apc.php';
            $cache = new Zend_Cache_Backend_Apc();
            $key = 'banconfig_' . APPLICATION_PATH . '_' . $environment;
            $cachedOptions = $cache->load($key);
            if ($cachedOptions !== false) {
                $options = $cachedOptions;
            }
            parent::__construct($environment, $options);
            if ($cachedOptions === false) {
                $cache->save($options, $key, array(), 60);
            }
        } else {
            parent::__construct($environment, $options);
        }
    }


}
