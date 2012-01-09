<?php

class Ban_Request extends Zend_Controller_Request_Http
{
    
    protected $_id;
    
    protected $_resourceType;
    
    protected $_modelClass;
    
    protected $_method;
    
    protected $_params = array(
        'filter' => array(),
        // 'order' => null,
        'dir' => 'ASC'
    );

    public function getId()
    {
        return $this->_id;
    }
    
    public function setId($id)
    {
        $this->_id = $id;
    }

    public function getResourceType()
    {
        return $this->_resourceType;
    }
    
    public function setResourceType($name)
    {
        $this->_resourceType = $name;
    }

    public function setModelClass($class)
    {
        $this->_modelClass = $class;
    }

    public function getModelClass()
    {
        return $this->_modelClass;
    }

    /**
     * Return the lower-case method by which the request was made
     *
     * @return string
     */
    public function getMethod()
    {
		// dump(get_class_methods($this));
		if ($this->_method === null) {
			if (($urlMethod = $this->getQuery('_method')) !== null) {
				$this->_method = strtolower($urlMethod);
			} else {
				$this->_method = strtolower($this->getServer('REQUEST_METHOD'));
			}
        }
        return $this->_method;
    }
    
    public function setMethod($method)
    {
        $this->_method = strtoupper($method);
    }

    public function setFilter($propertyName, $filter)
    {
        $this->_params['filters'][$propertyName] = $filter;
    }

    public function setFilters($filters)
    {
        $this->_params['filters'] = $filters;
    }
    
    public function getFilters()
    {
        return $this->_params['filters'];
    }
    
    public function setOrder($order)
    {
        $this->_params['order'] = $order;
    }

    public function getOrder()
    {
        return $this->_params['order'];
    }

    public function isRoot()
    {
        return $this->getResourceClass() === null;
    }
    
    public function parseRequestUri()
    {
        $parts = explode('/', $this->getPathInfo());
        $rClassParts = array();
        if (($numparts = count($parts)) > 0) {
            for ($i=0; $i<$numparts; $i=$i+2) {
                $rClassParts[$parts[$i]]= array_key_exists($i+1, $parts)? $parts[$i+1] : null;
            }
        }
        $cParts = array_map('ucfirst', array_keys($rClassParts));
        $apiRequest->setResourceClass(implode('_', $cParts));
        $apiRequest->setId(array_pop($rClassParts));
    }

    /**
     * Foreign key relations
     */
    public function setRelatedModelKey($model, $key)
    {
    }

    public static function createFromZendRequest(Zend_Controller_Request_Abstract $request, $baseUrl)
    {
		$apiRequest = new Ban_Request();
        $apiRequest->setBaseUrl($baseUrl);
        $apiRequest->setRequestUri($request->getRequestUri());
        $apiRequest->getPathInfo();
        $uri = $request->getPathInfo();
        if (!empty($baseUrl) && strpos($uri, $baseUrl) === 0) {
            $uri = trim(substr($uri, strlen($baseUrl)), '/');
        }
        $parts = explode('/', $uri);
        $rClassParts = array();
        if (($numparts = count($parts)) > 0) {
            for ($i=0; $i<$numparts; $i=$i+2) {
                $rClassParts[$parts[$i]]= array_key_exists($i+1, $parts)? $parts[$i+1] : null;
            }
        }
        $cParts = array_map('ucfirst', array_keys($rClassParts));
        $apiRequest->setResourceType(strtolower(implode('-', $cParts)));
        $apiRequest->setModelClass(implode('_', $cParts));
        $apiRequest->setId(array_pop($rClassParts));
        foreach ($rClassParts as $param => $val) {
            $apiRequest->setFilter($param . '_id', $val);
        }
        $urlParams = array_diff_assoc($request->getParams(), $request->getUserParams());
        foreach ($urlParams as $param => $val) {
            if ($param[0] === '_') {
                if ($param === '_method') {
					$apiRequest->setMethod($val);
					dd($param, $val); die;
                }
            } else {
                $apiRequest->setParam($param, $val);
            }
        }
        return $apiRequest;
    }
 
}
