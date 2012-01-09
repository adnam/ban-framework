<?php

class Ban_Property_AutoId extends Ban_Property_Abstract
{

    protected $_options = array(
        'index' => true,
        'primary' => true,
        'validator' => null
    );

    
    public function validate($value)
    {
        return true;
    }

}
