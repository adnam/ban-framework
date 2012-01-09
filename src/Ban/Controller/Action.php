<?php

class Ban_Controller_Action extends Zend_Controller_Action
{
    /**
     * @var Ban_Service_Interface
     */
    protected $_service;

    public function init()
    {
        $this->_helper->viewRenderer->setNoRender();
    }

    public function dispatch($action)
    {
        $request = $this->getRequest();
        $server = new Ban_Server('/api');
        $response = $server->handle($request);
        $this->setResponse($response);
        Zend_Controller_Front::getInstance()->setResponse($response);
    }

}
