<?php

class Ban_Model_Examples_Blog_Post extends Ban_Model_Abstract
{
	
    protected $_name = 'post';

    public function initProperties()
    {
        $this->addProperty('id', new Ban_Property_AutoId(), array());
        $this->addProperty('title', new Ban_Property_String(), array());
        $this->addProperty('body', new Ban_Property_String(), array());
        $this->addProperty('created', new Ban_Property_Datetime(), array());
        $this->addProperty('updated', new Ban_Property_Datetime(), array());
    }

    public function initRelations()
    {
        $this->addRelation('belongsTo', 'Ban_Model_Examples_Blog_User');
		$this->addRelation('hasMany', 'Ban_Model_Examples_Blog_Post_Comment');
    }
    
    public function initViews()
    {
    }

    public function initRoutes()
    {
        $this->addRoute('posts');
        $this->addRoute('posts/*', array('id'));
    }
}
