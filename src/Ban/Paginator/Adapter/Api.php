<?php
/**
 * @see Zend_Paginator_Adapter_Interface
 */
require_once 'Zend/Paginator/Adapter/Interface.php';

/**
 * @category   Zend
 * @package    Zend_Paginator
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Ban_Paginator_Adapter_Api implements Zend_Paginator_Adapter_Interface
{

    protected $_api; /* Ban_Client */
    protected $_resource; /* eg '/users' */
    protected $_params; /* Filter params */
    protected $_lastResult;

    /**
     * Constructor.
     *
     * @param Ban_Client $api
     * @param string $resourse url to attack
     * @param array $params parameters to pass to the API
     */
    public function __construct(Ban_Client $api, $resource, $params = array())
    {
        $this->_api = $api;
        $this->_resource = $resource;
        $this->_params = $params;
    }

    public function getFirstResult()
    {
        $params = array_merge(
            $this->_params,
            array('page' => 1, 'limit' => 1)
        );
        return $this->_api->get($this->_resource, $params);
    }

    public function getItems($offset, $itemCountPerPage)
    {
        $params = array_merge(
            $this->_params,
            array('page' => $offset/$itemCountPerPage)
        );
        $result = $this->_api->get($this->_resource, $params);
        $this->_lastResult = $result;
        return $result->result;
    }

    public function count()
    {
        if ($this->_lastResult instanceof Ban_Response) {
            return $this->_lastResult->total_records;
        }
        $first = $this->getFirstResult();
        return $first->total_records;
    }

}
