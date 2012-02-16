<?php

class Ban_Record_Abstract
{
	
    public function __construct(array $options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }
    
    public function __set($name, $value)
    {
        $method = 'set' . ucfirst($name);
        if (method_exists($this, $method)) {
            $this->method($value);
            return;
        }
        if (array_key_exists($name, $this->_properties)) {
            $this->_properties[$name] = $value;
            return;
        }
        throw new Ban_Exception_Server("Invalid property [$name]", 500);
    }
	
    public function __get($name)
    {
        $method = 'get' . $name;
        if (method_exists($this, $method)) {
            return $this->method();
        }
        if (array_key_exists($name, $this->_properties)) {
            return $this->_properties[$name];
        }
        throw new Ban_Exception_Server("Invalid property [$name]");
    }

    public function __call($method, $args)
    {
        if (strlen($method) > 3) {
            $prefix = substr($method, 0, 3);
            $property = lcfirst(substr($method, 3));
            if (array_key_exists($name, $this->_properties)) {
                if (count($args) === 0 && $prefix === 'get') {
                    return $this->_properties[$name];
                }
                elseif (count($args) === 1 && $prefix === 'set') {
                    $this->_properties[$name] = $args[0];
                    return $this;
                }
            }
        }
    }
    
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $this->__set($key, $value);
        }
        return $this;
    }

    public function toArray()
    {
        $asArray = array();
        foreach (self::getProperties() as $property) {
            if ($property[0] !== '_') {
                $asArray[$property] = $this->property;
            }
        }
        return $asArray;
    }

    public function toJson()
    {
        return Zend_Json::encode($this->toArray());
    }

}
