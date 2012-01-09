<?php

class Ban_Property_Uuid extends Ban_Property_AutoId
{

    protected $_options = array(
        'index' => true,
        'primary' => true,
        'validator' => null
    );

    
    public function validate($value)
    {
        try {
            $uuid = new Ban_Util_Uuid((string) $value);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }
    
    public function gen()
    {
        $uuid = new Ban_Util_Uuid();
        return $uuid;
    }

}
