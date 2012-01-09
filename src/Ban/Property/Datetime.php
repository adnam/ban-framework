<?php

class Ban_Property_Datetime extends Ban_Property_Abstract
{

    protected $_options = array(
        'index' => true,
        'primary' => false,
    );
    
    public function validate(&$value)
    {
        list($date, $time) = explode(' ', $value);
        if ($date===null || $time===null) {
            return false;
        }
        list($year, $month, $day) = explode('-', $date);
        if ($year===null || $month===null || $day===null) {
            return false;
        }
        list($hour, $minute, $second) = explode('-', $time);
        if ($hour===null || $minute===null || $second===null) {
            return false;
        }
        return (bool) mktime($hour, $minute, $second, $day, $month, $year);
    }

}
