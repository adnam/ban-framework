<?php

class Ban_Property_Password extends Ban_Property_String
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
