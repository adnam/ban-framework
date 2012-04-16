<?php

class Ban_Property_Enum extends Ban_Property_String
{
    
    protected $_options = array(
        'index' => true,
        'values' => array(),
    );
    
    public function validate(&$value)
    {
        return in_array($value, $this->_options['values']);
    }
}
