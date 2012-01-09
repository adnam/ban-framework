<?php

class Ban_Server
{
    
    protected $_responseCode = 200;
    protected $_baseUrl;
    protected $_cache;
    protected $_map;
    
    /**
     * Constructor
     *
     * @return Ban_Server
     */
    public function __construct($baseUrl = null)
    {
        set_exception_handler(array($this, "fault"));
        $this->_baseUrl = $baseUrl;
        $this->_map = Ban_Map::getInstance();
    }
    
    /**
     * Get the ban-api configuration
     * 
     * @return Zend_Config
     */
    public function getConfig()
    {
        return Zend_Registry::get('apiconfig');
    }

    /**
     * Get a cache instance, based on params in config
     *
     * @return Zend_Cache
     */
    protected function getCache()
    {
        $config = $this->getConfig();
        if ($this->_cache === null) {
            $config = $this->getConfig();
            // dump($config->cache->backEnd->options);
            $this->_cache = Zend_Cache::factory(
                $config->cache->frontEnd->name,
                $config->cache->backEnd->name,
                $config->cache->frontEnd->options->toArray(),
                $config->cache->backEnd->options->toArray()
            );
        }
        return $this->_cache;
    }

    public function getParams($request)
    {
        $params = $request->getParams();
        unset($params['controller']);
        return $params;
    }
    
    public function getContentType(&$result)
    {
        $ct = new Ban_ContentType(
            Ban_ContentType::MIME_JSON
        );
        if (isset($result['record'])) {
            $ct->view = $result['record']['view'];
            $ct->type = 'record';
        } elseif (isset($result['recordset'])) {
            $ct->view = $result['recordset']['view'];
            $ct->type = 'recordset';
        } elseif (isset($result['error'])) {
            $ct->type = 'error';
        }
        return $ct;
    }

    /**
     * Get the ETAG header value for result
     *
     * @return string ETAG
     */
    public function getEtag(&$result)
    {
        return 'etag';
    }

    public function handleFault($exception = null, $code = null)
    {
        $response = new Ban_Response();
        $response->setContentType(Ban_ContentType::MIME_JSON);
        $response->setType('error');
        $response->error = array(
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'trace' => $exception->getTrace()
        );
        return $response;
    }

    /**
     * Implement Zend_Server_Interface::fault()
     *
     * @param   string|Exception $fault Message
     * @param   int $code Error Code
     */
    public function fault($exception = null, $code = null)
    {
        if ($exception instanceof Ban_Exception) {
            $this->_responseCode = $exception->getCode();
        } else {
            $this->_responseCode = 500;
        }
        $response = $this->handleFault($exception, $code);
        $response->sendHeaders();
        $response->outputBody();
    }
    
    public function getResponse($request)
    {
        try {
            // dump($this->_map->getRoutes()->flatten());
            $path = $request->getPathInfo();
            list($model, $params) = $this->_map->getModelParams($path);
            if ($model === null) {
                throw new Ban_Exception("Resource [$path] not found on this server", 404);
            }
            foreach ($params as $param => $val) {
                $request->setParam($param, $val);
            }
            $service = $model->getService();
            if (array_key_exists('_method', $_GET)) {
                $serviceMethod = $_GET['_method'];
            } else {
                $serviceMethod = strtolower($request->getMethod());
            }
            $id = $this->getId($model, $params);
            $request->setParam($model->getPrimary(), $id);
            return $service->{$serviceMethod}($request);
            
        } catch (Exception $e) {
            return $this->handleFault($e);
        
        }
    }

    /**
     * Handle an incoming HTTP request
     *
     * @return Ban_Response $response
     */
    public function handle($request)
    {
        if (!$request instanceof Ban_Request) {
            // $request = new Ban_Request($this->_baseUri);
            $request = Ban_Request::createFromZendRequest($request, $this->_baseUrl);
        }
        $response = $this->getResponse($request);
        $response->setHttpResponseCode($this->_responseCode);
        $response->setHeader('etag', $this->getEtag($response->result));
        return $response;
    }

    /**
     * Respond to an incoming HTTP request, outputting headers + body
     * 
     * @return Ban_Response $response
     */
    public function respond($request)
    {
        $response = $this->handle($request);
        $response->sendHeaders();
        $response->outputBody();
    }
    
    /**
     * Pluck the ID from the url parameters, for a given model
     *
     * @return string id
     */
    public function getId($model, &$params)
    {
        $primary = $model->getPrimary();
        if(array_key_exists($primary, $params)) {
            return $params[$primary];
        }
        return null;
    }

}
