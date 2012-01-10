<?php

class Ban_Property_Abstract
{

    protected static $_defaultOptions = array(
        'type' => null,
        'index' => false,
        'primary' => false,
        'unique' => false,
        'validator' => null
    );

    protected $_options = array(
        'type' => null,
        'index' => false,
        'primary' => false,
        'unique' => false,
        'validator' => null
    );
    
    public function __construct($options = array())
    {
        $this->_options = array_merge(
            Ban_Property_Abstract::$_defaultOptions,
            $this->_options,
            $options,
            array('type' => $this->getType())
        );
    }
    
    public function getType()
    {
        return strtolower(str_replace('_', '-', get_class($this)));
    }   

    public function setPrimary($primary = true)
    {
        $this->_options['primary'] = (bool) $primary;
        return $this;
    }
    
    public function isPrimary()
    {
        return $this->_options['primary'];
    }

    public function setIndex($index = true)
    {
        $this->_options['index'] = (bool) $index;
        return $this;
    }
    
    public function isIndex()
    {
        return $this->_options['index'];
    }

    public function setUnique($unique = true)
    {
        $this->_options['unique'] = (bool) $unique;
        return $this;
    }
    
    public function isUnique()
    {
        return $this->_options['unique'];
    }

    public function setValidator(Ban_Validator_Abstract $validator)
    {
        $this->_options['validator'] = $validator;
        return $this;
    }
    
    public function getOptions()
    {
        return $this->_options;
    }

    public function getOption($name)
    {
        if (!array_key_exists($name, $this->_options)) {
            return null;
        }
        return $this->_options[$name];
    }

}
