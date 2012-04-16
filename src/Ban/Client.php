<?php
/**
 * A caching Rest client
 * 
 * @author Adam Hayward <adam@happy.cat>
 * @license Copyright (c) Adam Hayward 2011, all rights reserved
**/
class Ban_Client 
    extends Zend_Service_Abstract
    implements Ban_Client_Interface
{
    
    /**
     * @Zend_Uri REST service URI
     */
    protected $_serviceUri;

    /**
     * @array Configuration options
     *
     * http_client_options  array   array of options for the HTTP client, 
     *                              @see Zend_Http_Client
     */
    protected $_options = array(
        'http_client_options' => array(
            'useragent'       => 'Ban_Client',
            'timeout'         => 120,
            'username'        => null,
            'password'        => null,
        ),
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
    public function __construct($uri = null, $cache = null, $logger = null, $options = array())
    {

        if ($uri instanceof Zend_Uri_Http) {
            $this->_serviceUri = $uri;
        } else {
            $this->_serviceUri = Zend_Uri::factory($uri);
        }
        
        // Explicitly set the port number if necessary (used for the cache key)
        if (!$this->_serviceUri->getPort()) {
            $this->_serviceUri->setPort(80);
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

    public static function factory($options = array())
    {
        $defaults = array(
            'uri' => null,
            'logger' => null,
            'cache' => null,
            'options' => array()
        );
        $options = array_merge($defaults, $options);
        if (isset($options['logger']) && is_array($options['logger'])) {
            $logger = Zend_Log::factory($options['logger']);
        } else {
            $logger = null;
        }
        if (isset($options['cache']) && is_array($options['cache'])) {
            $cache = Zend_Cache::_makeBackend($options['cache']['name'], $options['cache']['options']);
        } else {
            $cache = null;
        }
        return new Ban_Client($options['uri'], $cache, $logger, $options['options']);
    }

    /* ********************************************************************* */
    /*                  Option handling functions                            */
    /* ********************************************************************* */

    protected function _setOptions($options = array())
    {
        foreach ($options as $name => $value) {
            if ($name === 'http_client_options') {
                $value = array_merge($this->_options['http_client_options'], $value);
            }
            $this->_setOption($name, $value);
        }
        self::getHttpClient()->setConfig($this->_options['http_client_options']);
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

    protected function _request($method, $path, &$params)
    {
        $uri = clone($this->_serviceUri);
        $uri->setPath(rtrim($uri->getPath(), '/') . '/' . ltrim($path, '/'));
        $httpClient = self::getHttpClient();
        $req = $httpClient->resetParameters()
            ->setEncType(Zend_Http_Client::ENC_URLENCODED)
            ->setUri($uri);
        switch ($method) {
            case Zend_Http_Client::HEAD:
            case Zend_Http_Client::GET:
            case Zend_Http_Client::OPTIONS:
                $response = $req->setParameterGet($params)->request($method);
                break;
            
            case Zend_Http_Client::POST:
            case Zend_Http_Client::PUT:
            case Zend_Http_Client::DELETE:
            case Zend_Http_Client::MERGE:
            case Zend_Http_Client::TRACE:
                $response = $req->setParameterPost($params)->request($method);
                break;
        }
        return Ban_Response::createFromZendHttpResponse($response);
    }
    
    public function handleResponse(Zend_Http_Response $response)
    {
        $contentType = Ban_ContentType::fromString($response->getHeader('content-type'));
        // $mime = $contentType->getMime();
        try {
            switch ($contentType->getMime()) {
            case Ban_ContentType::MIME_JSON:
                $result = Zend_Json::decode($response->getBody());
                break;
            case Ban_ContentType::MIME_XML:
                $result = simplexml_load_string($response->getBody());
                break;
            }
        } catch (Exception $e) {
            dump($e);
        }
        return $result;
    }

    function get($path, $params = array())
    {
        return $this->_request(Zend_Http_Client::GET, $path, $params);
    }
    
    function put($path, $params = array())
    {
        return $this->_request(Zend_Http_Client::PUT, $path, $params);
    }

    function merge($path, $params = array())
    {
        return $this->_request(Zend_Http_Client::MERGE, $path, $params);
    }

    function post($path, $params = array())
    {
        return $this->_request(Zend_Http_Client::POST, $path, $params);
    }

    function delete($path, $params = array())
    {
        return $this->_request(Zend_Http_Client::DELETE, $path, $params);
    }

    function options($path, $params = array())
    {
        return $this->_request(Zend_Http_Client::OPTIONS, $path, $params);
    }

    function trace($path, $params = array())
    {
        return $this->_request(Zend_Http_Client::TRACE, $path, $params);
    }

}
