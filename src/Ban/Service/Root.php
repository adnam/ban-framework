<?php

class Ban_Service_Root
    extends Ban_Service_Abstract
    implements Ban_Service_Interface
{

    protected $_model;
    
    protected $_dao;
    
    public function __construct(Ban_Model_Interface $model)
    {
        $this->_model = $model;
    }

    public function options(Ban_Request $request)
    {
        return array();
    }
    
    public function head(Ban_Request $request)
    {
    }
    
    public function trace(Ban_Request $request)
    {
    }
    
    public function get(Ban_Request $request)
    {
        $map = Ban_Map::getInstance();
        $response = $this->createResponse('root');
        $response->options = $this->options($request);
        $response->result = array(
            'namespaces' => $map->getNamespaces(),
            'models' => $map->getModelNames(),
            'routes' => $map->getRoutes()->flatten(),
        );
        return $response;
    }
    
    public function put(Ban_Request $request)
    {
        throw new Exception(
            __method__ . " is not implemented",
            Ban_Exception::BAD_METHOD
        );
    }
    
    public function merge(Ban_Request $request)
    {
        throw new Exception(
            __method__ . " is not implemented",
            Ban_Exception::BAD_METHOD
        );
    }
    
    public function post(Ban_Request $request)
    {
        throw new Exception(
            __method__ . " is not implemented",
            Ban_Exception::BAD_METHOD
        );
    }
    
    public function delete(Ban_Request $request)
    {
        throw new Exception(
            __method__ . " is not implemented",
            Ban_Exception::BAD_METHOD
        );
    }


}
