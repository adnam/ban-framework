<?php

class Ban_Model_Examples_Blog_User extends Ban_Model_Abstract
{
	
    protected $_name = 'user';
    
    protected $_indexes = array(
        'id',
        'username',
        'email'
    );
    
    /**
     * Names of properties which may be ordered
     *
     * @var array
     */
    protected $_orderable = array('id', 'username', 'email', 'created', 'updated');
    
    public function initProperties()
    {
        $this->addProperty('id', new Ban_Property_AutoId());
        $this->addProperty('username', new Ban_Property_String());
        $this->addProperty('email', new Ban_Property_Email());
        $this->addProperty('password', new Ban_Property_Password());
        $this->addProperty('created', new Ban_Property_Datetime());
        $this->addProperty('updated', new Ban_Property_Datetime());
    }

    public function initRelations()
    {
        $this->addRelation('hasMany', 'Ban_Model_Examples_Blog_Post');
        return $this;
    }

    public function initViews()
    {
    }

    public function initRoutes()
    {
        $this->addRoute('users');
        $this->addRoute('users/*', array('id'));
    }

}
