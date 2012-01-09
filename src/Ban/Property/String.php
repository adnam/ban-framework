<?php

class Ban_Property_String extends Ban_Property_Abstract
{

    protected $_options = array(
        'index' => false,
        'primary' => false,
    );
    
    public function validate(&$value)
    {
        return is_string($value);
    }

}
