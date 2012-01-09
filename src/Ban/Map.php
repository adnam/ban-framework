<?php

class Ban_Map
{
    /**
     * Keyname used to cache this map
     *
     * @var string
     */
    protected static $_cacheKey = 'ban_map';   
    
    /**
     *
     * @var Zend_Cache
     */
    protected static $_cache;

    /**
     * Routs mapping URIs to models and parameters
     *
     * @var Ban_Route
     */
    protected $_routes;

    /**
     * Array of models available
     *
     * @var array
     */
    protected $_models = array();
    
    /**
     * Array mapping model names to class names
     *
     * @var array
     */
    protected $_classNames = array();

    /**
     * Namespaces of models
     *
     * @var string
     */
    protected static $_modelNamespaces = array();

    /**
     * Singleton instance
     *
     * @var Ban_Map
     */
    protected static $_instance;

    protected function __construct()
    {
    }

    /**
     * Fetch the config params from the registry
     *
     * @return Zend_Config
     */
    public static function getConfig()
    {
        return Zend_Registry::get('apiconfig');
    }

    /**
     * Get the cache object
     * 
     * @return Zend_Cache
     */
    protected static function getCache()
    {
        if (self::$_cache === null) {
            $config = self::getConfig();
            // dump($config->cache->backEnd->options);
            self::$_cache = Zend_Cache::factory(
                $config->cache->frontEnd->name,
                $config->cache->backEnd->name,
                $config->cache->frontEnd->options->toArray(),
                $config->cache->backEnd->options->toArray()
            );
        }
        return self::$_cache;
    }

    /**
     * Reset the singleton instance
     * (Useful for testing)
     */
    public function reset()
    {
        self::$_instance = null;
    }

    /**
     * Initialise the models and construct the map
     */
    public function initModels()
    {
		foreach ($this->getPaths() as $ns => $path) {
            // $files = glob($path . DIRECTORY_SEPARATOR . '*.php');
            $files = $this->getFilesInDirectoryRecursively($path);
            foreach ($files as $file) {
                $class = $ns . str_replace(DIRECTORY_SEPARATOR, '_', substr($file, 0, -4));
                if (!class_exists($class)) {
                    continue;
                }
                $reflected = new ReflectionClass($class);
                if ($reflected->isSubclassOf('Ban_Model_Abstract') && !$reflected->isAbstract()) {
                    $modelInstance = $class::getInstance();
                    $this->_models[$class] = $modelInstance;
                    $this->_classNames[$modelInstance->getName()] = $class;
                    $modelInstance->setMap($this);
                }
            }
        }
    }

    /**
     * Get a list of all models
     *
     * @return array
     */
    public function getModels()
    {
        return $this->_models;
    }

    public function getModel($modelName)
    {
        if (!array_key_exists($modelName, $this->_classNames)) {
            throw new Ban_Exception("Unknown model [$modelName]", 500);
        }
        return $this->_models[$this->_classNames[$modelName]];
    }

    /**
     * Get a list of model names
     *
     * @return array
     */
    public function getModelNames()
    {
        $names = array();
        foreach ($this->_models as $model) {
            $names[] = $model->getName();
        }
        return $names;
    }

    /**
     * Get a singleton instance of map
     * 
     * First checks the static cache, and returns the instance if found
     *
     * Then attempts to re-construct it from a serialized cached version
     * (stored in the external cache, for example memcached).
     * 
     * Finally, creates a new instance and calls initModels(), storing 
     * the instance in both the static cache and the external cache.
     *
     * @return Ban_Map
     */
    public static function getInstance()
    {
        // 1. Try to load from instance variable
        if (self::$_instance instanceof Ban_Map) {
            return self::$_instance;
        }

        $cache = self::getCache();
        // $cache = null;

        // 2. Try to load from cache
        if ($cache !== null) {
            if ($serialized = $cache->load(static::$_cacheKey)) {
                self::$_instance = unserialize($serialized);
                return self::$_instance;
            }
        }

        // 3. Create, cache and return a fresh instance
        self::$_instance = new Ban_Map();
        self::$_instance->initModels();
        if ($cache !== null) {
            $cache->save(serialize(self::$_instance), static::$_cacheKey, array(), 5);
        }
        return self::$_instance;
    }

    public function getNamespaces()
    {
        if (empty(static::$_modelNamespaces)) {
            $config = Zend_Registry::get('apiconfig');
            try {
                static::$_modelNamespaces = $config->model->namespaces->toArray();
            } catch (Exception $e) {
                throw new Ban_Exception_Server(
                    "API Error: no models have been defined - model.namespaces"
					. " must be defined in the config file", 500
                );
            }
        }
        return static::$_modelNamespaces;
    }
    
    public function getPaths()
    {
        $paths = explode(PATH_SEPARATOR, get_include_path());
        $modelDirs = array();
        foreach (self::getNamespaces() as $ns) {
            $dir = str_replace('_', DIRECTORY_SEPARATOR, $ns);
            foreach ($paths as $path) {
				$path = realpath($path);
				$modelDir = realpath($path . DIRECTORY_SEPARATOR . $dir);
                if ($modelDir !== false) {
                    $modelDirs[$ns] = $modelDir;
                }
			}
        }
        return array_unique($modelDirs);
    }
    
    public function getFilesInDirectoryRecursively($dir)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        $len = strlen($dir);
        $files = array();
        foreach ($iterator as $path) {
            if ($path->isDir()) {
                continue;
            }
            if ($len>1 && substr($path->getFilename(), 0, 1)=='.') {
                continue;
            }
            if ($len>4 && substr($path->getFilename(), -4, 4)=='.php') {
                $files[] = substr($path->getPathname(), $len);
            }
        }
        return $files;
    }

    public function addRoute($model, $route, $params)
    {
        if (!$this->_routes instanceof Ban_Route) {
            $this->_routes = new Ban_Route('/', array(), Ban_Model_Root::getInstance());
        }
        $this->_routes->addRoute($route, $params, $model);
    }
    
    public function getRoutes()
    {
        return $this->_routes;    
    }
    
    /**
     * Return a model and path parameters for a given uri
     * 
     * return array tuple ($model, $parameters)
     */
    public function getModelParams($uri)
    {
        if (!$this->_routes) {
            throw new Ban_Exception("Resource [$uri] not found on this server", 404);
        }
        return $this->_routes->match($uri);
    }

    public function toArray()
    {
        $asArray = array(
            'modelNamespaces' => static::$_modelNamespaces,
            'routes' => $this->_routes->flatten(),
        );
        return $asArray;
    }


}
