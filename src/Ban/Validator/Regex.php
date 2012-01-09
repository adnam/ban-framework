<?php

class Ban_Model_Validator_Regex extends Ban_Model_Validator_Abstract
{

    protected $regex;

    public function __construct($regex)
    {
        $this->regex = $regex;
    }

    public function validate($value)
    {
        return preg_match($this->regex, $value);
    }

}
