<?php

class Ban_Model_Validator_String extends Ban_Model_Validator_Abstract
{

    public function validate($value)
    {
        return is_string($value);
    }

}
