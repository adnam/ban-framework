<?php

abstract class Ban_Service_Abstract implements Ban_Service_Interface
{

    protected $_model;

    protected $_dao;
    
    public function __construct($model)
    {
        $this->_model = $model;
    }

    public function normalizeParams($params = array())
    {
        // Remove '_method' para is present
        // TODO: move this to Ban_Request
        if (array_key_exists('_method', $params)) {
            unset($params['_method']);
        }
        
        $default = array(
            'limit' => 10,
            'page' => 0,
            'filter' => array(),
            'order' => $this->_model->getPrimary(),
            'dir' => 'ASC',
        );
        if (array_key_exists('limit', $params)) {
            $params['limit'] = (int) $params['limit'];
        } else {
            $params['limit'] = $default['limit'];
        }
        
        if (array_key_exists('page', $params)) {
            $params['page'] = (int) $params['page'];
        } else {
            $params['page'] = $default['page'];
        }
        if (!array_key_exists('filter', $params) || empty($params['filter']) || !is_array($params['filter'])) {
            $params['filter'] = $default['filter'];
        }
        
        if (!array_key_exists('order', $params)) {
            $params['order'] = $default['order'];
        }
        
        if (array_key_exists('dir', $params)) {
            $params['dir'] = strtoupper($params['dir']);
            if (!in_array($params['dir'], array('ASC', 'DESC'))) {
                $params['dir'] = $default['dir'];
            }
        }
        return $params;
    }

    protected function getModel($modelName = null)
    {
        if ($modelName === null) {
            return $this->_model;
        }
        return $this->_model->getMap()->getModel($modelName);
    }
    
    protected function getDao($modelName = null)
    {
        if ($modelName === null) {
            return $this->_model->getDao();
        }
        return $this->_model->getMap()->getModel($modelName)->getDao();
    }

    protected function createResponse($type)
    {
        $response = new Ban_Response();
        $response->setContentType(Ban_ContentType::MIME_JSON);
        $response->setType($type);
        return $response;
    }

    public function options(Ban_Request $request)
    {
        $map = $this->getDao();
        $response = $this->createResponse('recordset');
        $response->primary = $map->getPrimary();
        $response->indexes = $map->getIndexes();
        $response->keys = $map->getKeys();
        $response->views = $map->getViews();
        $response->orderable = $map->getOrderable();
        $response->properties = $map->getProperties();
        return $response;
    }
    
    public function head(Ban_Request $request)
    {
    }

    public function trace(Ban_Request $request)
    {
        return $request;
    }

    public function get(Ban_Request $request)
    {
        $dao = $this->getDao();
        if ($request->id === null) {
            $params = $this->normalizeParams($request->getParams());
            $result = $this->createResponse('recordset');
            $result->total_results = $dao->count($params['filter']);
            $result->total_records = $dao->count();
            $result->limit = $params['limit'];
            $result->page = $params['page'];
            $result->getContentType()->view = $dao->getView();
            $result->result = $dao->fetchAll($params['filter'], $params['limit'], $params['page'], $params['order'], $params['dir']);
        } else {
            $result = $this->createResponse('record');
            $result->getContentType()->view = $dao->getView();
            $result->result = $dao->get($request->id);
        }
        return $result;
    }

    public function put(Ban_Request $request)
    {
        $params = $this->normalizeParams($request->getParams());
        $row = array();
        foreach ($this->_model->getProperties() as $name => $property) {
            if (array_key_exists($name, $params)) {
                $row[$name] = $params[$name];
            }
        }
        $id = $this->getDao()->save($row);
        $result = $this->createResponse('recordid');
        $result->id = $id;
        return $result;
    }
    
    public function merge(Ban_Request $request)
    {
    }
    
    public function post(Ban_Request $request)
    {
        $params = $this->normalizeParams($request->getParams());
        $row = array();
        foreach ($this->_model->getProperties() as $name => $property) {
            if (array_key_exists($name, $params)) {
                if ($property instanceof Ban_Property_Password) {
                    $row[$name] = $property->getStorageValue($params[$name]);
                } else {
                    $row[$name] = $params[$name];
                }
            }
        }
        $id = $this->getDao()->save($row);
        $result = $this->createResponse('recordid');
        $result->id = $id;
        return $result;
    }
    
    public function delete(Ban_Request $request)
    {
        if ($request->id === null) {
            throw new Ban_Exception_Client("You can only delete a specific resource", 401);
        }
        $deleted = $this->getDao()->delete(array("id = ?" => $request->id));
        if ($deleted < 1) {
            throw new Ban_Exception_Client("Resource with id [{$request->id}] does not exist", 404);
        }
        $result = $this->createResponse('recordid');
        $result->id = $request->id;
        return $result;
    }

}
