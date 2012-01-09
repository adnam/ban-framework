<?php

class Ban_Model_Examples_Blog_Post_Comment extends Ban_Model_Abstract
{

    protected $_name = 'user_comment';

    public function initProperties()
    {
        $this->addProperty('id', new Ban_Property_AutoId(), array());
        $this->addProperty('comment', new Ban_Property_String(), array());
        $this->addProperty('created', new Ban_Property_Datetime(), array());
        $this->addProperty('updated', new Ban_Property_Datetime(), array());
    }

    public function initRelations()
    {
        $this->addRelation('belongsExclusive', 'Ban_Model_Example_Post');
        $this->addRelation('belongsTo', 'Ban_Model_Example_User');
    }
    
    public function initViews()
    {
    }

    public function initRoutes()
    {
        $this->addRoute('users/*/comments', array('user_id'));
        $this->addRoute('users/*/comments/*', array('user_id', 'id'));
    }

}
