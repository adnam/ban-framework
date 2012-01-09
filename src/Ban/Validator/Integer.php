<?php

class Ban_Model_Validator_Integer extends Ban_Model_Validator_Abstract
{
    
    protected $min;
    
    protected $max;

    public function __construct($min = 0, $max = PHP_INT_MAX)
    {
        $this->min = $min;
        $this->max = $max;
    }

    public function validate($value)
    {
        return ($value >= $min && $value <= $max);
    }

}

