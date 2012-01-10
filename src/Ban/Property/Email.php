<?php

class Ban_Property_Email extends Ban_Property_String
{
    
    protected $_emailValidator;

    protected $_options = array(
        'name' => null,
        'index' => true,
        'primary' => false,
        'unique' => true,
    );
    
    public function validate(&$value)
    {
        if ($this->_emailValidator === null) {
            $this->_emailValidator = new Zend_Validate_EmailAddress();
        }
        if ($this->_emailValidator->isValid($value)) {
            return true;
        }
        return false;
    }

}
