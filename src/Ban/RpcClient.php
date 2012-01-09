<?php
/**
 * A caching Rest client
 * 
 * @author Adam Hayward <adam@happy.cat>
 * @license Copyright (c) Adam Hayward 2011, all rights reserved
**/
class Ban_Client extends Zend_Service_Abstract
{
    /*
     * @var bool
     */
    public $cacheResult = false;

    /*
     * @var Zend_Uri
     */
    protected $_uri;

    /**
     * @var Zend_Cache cache object
     */
    protected $_cache;
    
    /*
     * @var int Cache timeout in seconds
     */
    protected $_timeout = 0;
    
    /*
     * @array Configuration options
     *
     * raise_cache_errors   bool    Should cache exceptions be raised (true)
     *                              or simply logged (false).
     * cache_class          string  Classname of cache object, must be of type
     *                              Zend_Cache
     * cacheable_methods    array   List of http methods which may be cached.
     *                              By default, get and post requests may be
     *                              cached (yes, post methods may be cached).
     * http_client_options  array   array of options for the HTTP client, 
     *                              @see Zend_Http_Client
     */
    protected $_options = array(
        'raise_cache_errors' => true,
        'log_cached_calls' => false,
        'cache_class' => 'Zend_Cache_Backend_File',
        'cache_options' => null
    );

    /**
     * @see Zend_Log
     */
    protected $_logger;
    
    /**
     * Constructor
     *
     * @param <string>      uri of API service
     * @param <Zend_Cache>  optional cache object
     * @param <Zend_Log>    optional logger object
     * @param array         optional configuration options
     * @throws <Exception>
     */
    public function __construct($uri = null, $cache = null, $logger = null, $options = null)
    {

        if ($uri instanceof Zend_Uri_Http) {
            $this->_uri = $uri;
        } else {
            $this->_uri = Zend_Uri::factory($uri);
        }
        
        // Explicitly set the port number if necessary (used for the cache key)
        if (!$this->_uri->getPort()) {
            $this->_uri->setPort(80);
        }
        $this->_setOptions($options);
        if ($cache !== null) {
            $this->_cache = $cache;
        }
        if ($logger !== null && !($logger instanceof Zend_Log)) {
            throw new Exception("Logger must be an instance of Zend_Log");
        }
        $this->_logger = $logger;
    }
    
    /*
	 * Return the URI of the REST service
     *
     * @return Zend_Uri_Http    uri
     */
    public function _getUri()
    {
        return $this->_uri;
    }

    /* ********************************************************************* */
    /*                  Option handling functions                            */
    /* ********************************************************************* */

    protected function _setOptions($options = null)
    {
        while (list($name, $value) = each($options)) {
            if ($name === 'http_client_options') {
                self::getHttpClient()->setConfig($value);
            } else {
                $this->_setOption($name, $value);
            }
        }
    }

    /**
     * Set an option
     *
     * @param  string $name
     * @param  mixed  $value
     * @throws Exception
     * @return void
     */
    public function _setOption($name, $value)
    {
        if (!is_string($name)) {
            throw new Exception("Incorrect option name [$name]: expected a string");
        }
        if (array_key_exists($name, $this->_options)) {
            $this->_options[$name] = $value;
        }
    }
    
    /* ********************************************************************* */
    /*                   Cache handling functions                            */
    /* ********************************************************************* */

    /*
     * Get the cache object
     *
     * @return Zend_Cache object
     */
    public function _getCache()
    {
        if ($this->_cache === null) {
            $cacheClass = $this->_options['cache_class'];
            $this->_cache = false;
            $cacheOptions = $this->_options['cache_options'];
            if ($cacheOptions !== null) {
                $this->_cache = new $cacheClass($cacheOptions);
            }
        }
        return $this->_cache;
    }
    
    /*
     * Get the cache key for a given request
     *
     * @param <string>  method name
     * @param <string>  http method name
     * @param <array>   arguments
     * @return <string> cache keyname
     */
    public function _getCacheKey($method, $args)
    {
        $uri = (string) $this->_uri;
        $key = implode('/', array($uri, $method, md5(serialize($args))));
        return $key;
    }

    /*
     * Get the cache content for given method/args
     *
     * @param <string>  method name
     * @param <array>   arguments
     * @return <string> cache data or false if non-exietent
     * @throws <Zend_Cache_Exception>
     */
    protected function _cacheGet(&$method, &$args)
    {
        $key = $this->_getCacheKey($method, $args);
        try {
            $cache = $this->_getCache();
            if ($cache instanceof Zend_Cache_Backend) {
                $cached = $this->_getCache()->load($key);
            } else {
                return false;
            }
        }
        catch (Zend_Cache_Exception $e) {
            if ($this->_options['raise_cache_errors']) {
                throw $e;
            } else {
                $this->_log(__class__ . '::' . __function__ . '() : ' . $e->getMessage());
                $cached = false;
            }
        }
        return $cached;
    }
    
    /*
     * Set the cache content for given method/args
     *
     * @param <string>  method name
     * @param <array>   arguments
     * @param <string>  data
     * @return <string> bool if cache was set correctly
     * @throws <Zend_Cache_Exception>
     */
    protected function _cacheStore(&$method, &$args, &$data)
    {
        $key = $this->_getCacheKey($method, $args);
        $result = false;

        // caching result for API::$method() with key [$key], timeout [{$this->_timeout}]");
        try {
            $cache = $this->_getCache();
            if ($cache instanceof Zend_Cache_Backend) {
                $result = $this->_getCache()->save($data, $key, array(), $this->_timeout);
            }
        }
        catch (Zend_Cache_Exception $e) {
            if ($this->_options['raise_cache_errors']) {
                throw $e;
            } else {
                $this->_log(__class__ . '::' . __function__ . '(): ' . $e->getMessage());
                $result = false;
            }
        }
        // $this->_getLogger()->debug("... Result [" . var_export($result, 1) . "]");
        return $result;
    }

    /*
     * Set the cache timeout
     * 
     * Chainable function
     *
     * @param <integer> timeout in seconds
     * @return <Ban_Client> this
     */
    public function cache($timeout)
    {
        $this->_timeout = (int) $timeout;
        return $this;
    }
    
    /* ********************************************************************* */
    /*                   Logging-related functions                           */
    /* ********************************************************************* */

    /**
     * Get the logger object, create one if not set
     *
     * @return void
     */
    protected function _getLogger()
    {
        if ($this->_logger === null) {
            // Create a default logger to the standard output stream
            require_once 'Zend/Log.php';
            require_once 'Zend/Log/Writer/Stream.php';
            $this->_logger = new Zend_Log(new Zend_Log_Writer_Stream('php://output'));
        }
        return $this->_logger;
    }

    /**
     * Log a message at the WARN (4) priority.
     *
     * @param  string $message
     * @return void
     */
    protected function _log($message, $priority = 4)
    {
        $this->_getLogger()->log($message, $priority);
    }

    /* ********************************************************************* */
    /*                            RPC methods                                */
    /* ********************************************************************* */

    /*
     * @return Zend_Http_Response
     */
    public function post($method, $args)
    {
        return $this->_post($method, $args);
    }
    
    /*
     * @return Zend_Http_Response
     */
    protected function _post(&$method, &$args)
    {
        /**
         * Get the HTTP client and configure it for the endpoint URI.  Do this each time
         * because the Zend_Http_Client instance is shared among all Zend_Service_Abstract subclasses.
         */
        $httpClient = self::getHttpClient();

        $req = $httpClient->resetParameters()
            ->setEncType(Zend_Http_Client::ENC_URLENCODED)
            ->setUri($this->_uri);

        return $req->setParameterPost('method', $method)
            ->setParameterPost('args', serialize($args))
            ->request(Zend_Http_Client::POST);
    }
    
    public function _handleResponse($response)
    {
        $unserialized = @unserialize($response);
        if ($unserialized === false && $response !== 'b:0;') {
            // De-serialization failed for some reason
            if ($last = error_get_last()) {
                throw new Ban_Exception(
                    "Badly formatted response from [" . (string) $this->_uri 
                    . "]: " . $last['message']
                );
            }
        }
        $response = $unserialized;
        if (!is_array($response)) {
            throw new Ban_Exception(
                "Badly formatted response from [" . (string) $this->_uri 
                . "]: expected a php serialized array"
            );
        }
        if (!array_key_exists('status', $response)) {
            throw new Ban_Exception(
                "Badly formatted response from [" . (string) $this->_uri . "]: expected a status"
            );
        }
        if (!array_key_exists('response', $response)) {
            throw new Ban_Exception(
                "Badly formatted response from [" . (string) $this->_uri . "]: expected a response body"
            );
        }
        if ($response['status'] !== 'success') {
            $this->_handleFailedResponse($response);
        }
        return $response['response'];
    }
    
    public function _handleFailedResponse(&$response)
    {
        $message = null;
        $host = $this->_uri->getHost();
        $errorCode = 0;
        $exceptionClass = 'Ban_Exception';
        
        if (isset($response['response']['message']) && !empty($response['response']['message'])) {
            $message = $response['response']['message'];
        } elseif (is_string($response['response'])) {
            $message = $response['response'];
        }
        if (isset($response['response']['class'])) {
            $exceptionClass = $response['response']['class'];
            if (!class_exists($exceptionClass)) {
                throw new Ban_Exception(
                    "Api [$host] threw a [$exceptionClass] exception, but unable to "
                    . "instantiate locally. Message was: [$message]"
                );
            } elseif ($exceptionClass !== 'Exception'
                && !is_subclass_of($exceptionClass, 'Exception')) {
                throw new Ban_Exception(
                    "Api [$host] claims to have thrown an [$exceptionClass] "
                    . "exception, which is not a subclass of 'Exception'! "
                    . "Message was: [$message]"
                );
            }
        }
        if (is_array($response['response']) && array_key_exists('code', $response['response'])) {
            $errorCode = $response['response']['code'];
        }
        throw new $exceptionClass("Api Error [{$this->_uri}{$this->_method}]: $message", $errorCode);
    }

    /**
     * Method call overload
     *
     * Allows calling RCP actions as object methods
     * <code>
     * $response = $client->sayHello('Foo', 'Manchu');
     * </code>
     * 
     * Additionally, a cache length can be specified. The following will cache
     * the result for 300 seconds, or return a cached copy if available.
     * <code>
     * // Chaining calls
     * $response = $client->cache(300)->sayHello('Foo', 'Manchu');
     *
     * // Sequential calls:
     * $client->cache(300);
     * $response = $client->sayHello('Foo', 'Manchu');
     * </code>
     *
     * @param string $method Method name
     * @param array $args Method args
     * @return Zend_Rest_Client_Result|Zend_Rest_Client Zend_Rest_Client if using
     * a remote method, Zend_Rest_Client_Result if using an HTTP request method
     * or Ban_Client_Result if the result is from the cache
     * @throws <Zend_Cache_Exception>
     * @throws <Ban_Exception>
     */
    public function __call($method, $args)
    {
        $body = false;
        $this->cacheResult = false;
        $this->_method = $method;
        try {
            if ($this->_timeout > 0) {
                // Attempt to retrieve the result from the cache
                $body = $this->_cacheGet($method, $args);
            }

            // debug info
            $cached = !$body === false;
            $logCached = array_key_exists('log_cached_calls', $this->_options) && $this->_options['log_cached_calls'];

            if (!$body === false) {
                $this->cacheResult = true;
            } else {
                $result = $this->_post($method, $args);
                $body = trim($result->getBody());
            }
            
            $result = $this->_handleResponse($body);

            // If we get here, its a successful response
            if ($this->cacheResult === false && $this->_timeout > 0) {
                $this->_cacheStore($method, $args, $body);
            }
        }
        catch (Exception $e) {
            $this->cache(0);
            throw $e;
        }
        $this->cache(0);
        return $result;
    }
}

