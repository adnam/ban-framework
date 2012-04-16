<?php

class Ban_Paginator_Adapter_Aggregate implements Zend_Paginator_AdapterAggregate
{

    protected $_api; /* Ban_Client */
    protected $_resource; /* eg '/users' */
    protected $_params; /* Filter params */

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

    /**
     * Return a fully configured Paginator Adapter from this method.
     *
     * @return Zend_Paginator_Adapter_Interface
     */
    public function getPaginatorAdapter()
    {
        return new Ban_Paginator_Adapter_Api($this->_api, $this->_resource, $this->_params);
    }


}
