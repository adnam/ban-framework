<?php

class Ban_Service_UserPassword extends Ban_Service_Abstract
{

    public function index($id, $params)
    {
        return array();
    }
    
    public function checkPassword($password, $stored)
    {
        return sha1($password) === $stored;
    }

    public function get($id, $params = array())
    {
        $user = $this->getDao()->get($id);
        $result = array(
            'options' => $this->options(),
            'record' => array(
                'view' => $this->getDao()->getView(),
                $this->_resourceClass => $this->checkPassword($params['password'], $user['password']),
            )
        );
        return $result;
    }

    public function put($id, $params)
    {
    }

    public function patch($id, $params)
    {
    }

    public function post($id, $params)
    {
    }

    public function delete($id, $params = array())
    {
    }


}
