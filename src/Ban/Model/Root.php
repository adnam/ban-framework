<?php

class Ban_Model_Root extends Ban_Model_Abstract
{
	
    protected $_name = 'root';
    
    protected $_indexes = array();

    /**
     * Classname of service
     *
     * @var string
     */
    protected $_serviceClass = 'Ban_Service_Root';

    public function initProperties()
    {
    }

    public function initRelations()
    {
    }

    public function initViews()
    {
    }

    public function initRoutes()
    {
    }

}
