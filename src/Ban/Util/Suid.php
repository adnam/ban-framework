<?php
/**
 * Short Unique id ('suid') Class: a shorter alternative to UUIDs
 *
 * Contains 6 bytes (48-bits) packed into 8 characters, matching ^[_a-zA-Z0-9.]{8}$
 * We could, of course, use BASE64-encoding, but this method sneekily avoids 
 * characters that need special URL encoding, such as '+', '/' and '='.
 * 
 * There are about 2.8 x 10^14 different SUIDs available (> 280 trillion). For
 * many purposes, the chances of collision are vanishingly small.
 * 
 * Examples:
 * <code>
 *  
 *  $suid = new Ban_Util_Suid();
 *  
 *  print $uuid;
 *  // Gives, for example, WqOUqeP3
 *  
 *  // Create a UUID from an 8-char string
 *  $u2 = new Ban_Util_Suid('WqOUqeP3');
 *
 *  // Get a binary representation
 *  $u2->bin(); // Gives
 *
 * </code>
 *
 * @author      Adam Hayward <adam at happy dot cat>
 * @copyright   Copyright (c) 2010 Adam Hayward (http://happy.cat)
 * @license     New BSD License, http://svn.happy.cat/public/LICENSE.txt
**/

class Ban_Util_Suid
{
    
    protected $_bin;
    protected $_str;

    const b64chars = '_abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789.';
    protected static $ords;

    function __construct($suid = null)
    {
        if (null === $suid){
            $this->_bin = Ban_Util_Suid::gen();
            return;
        }
        $len = strlen($suid);
        if ($len === 6) {
            $this->_bin = $suid;
        
        } elseif ($len === 8) {
            $this->_str = $suid;
        
        } else {
            throw new Exception('Invalid SUID');
        }
    }
    
    /**
     * Generate a 48-bit binary SUID
     */
    public static function gen()
    {
        return pack('n*',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    public function __get($key)
    {
        if ($key === 'id') {
            return $this;    
        }
    }

    /**
     * Return a string representation
     * 
     * @return string   8-character string represetation of SUID
     */
    public function str()
    {
        if (null === $this->_str) {
            $ints = unpack('n*', $this->_bin);
            // Converts 3 16-bit numbers into 8 6-bit numbers. Way over-optimized.
            $this->_str  = substr(Ban_Util_Suid::b64chars, ((($ints[1])&(0xfc00))>>10), 1);
            $this->_str .= substr(Ban_Util_Suid::b64chars, ((($ints[1])&(0x03f0))>>4), 1);
            $this->_str .= substr(Ban_Util_Suid::b64chars, (((($ints[1])&(0x000f))<<2) | ((($ints[2])&(0xc000))>>14)), 1);
            $this->_str .= substr(Ban_Util_Suid::b64chars, ((($ints[2])&(0x3f00))>>8), 1);
            $this->_str .= substr(Ban_Util_Suid::b64chars, ((($ints[2])&(0xfc))>>2), 1);
            $this->_str .= substr(Ban_Util_Suid::b64chars, (((($ints[2])&(0x3))<<4) | ((($ints[3])&(0xf000))>>12)), 1);
            $this->_str .= substr(Ban_Util_Suid::b64chars, ((($ints[3])&(0xfc0))>>6), 1);
            $this->_str .= substr(Ban_Util_Suid::b64chars, (($ints[3])&(0x3f)), 1);
        }
        return $this->_str;
    }

    public function ord($chr)
    {
        if (Ban_Util_Suid::$ords === null) {
            Ban_Util_Suid::$ords = array();
            for ($i=0; $i<64; ++$i) {
                $char = substr(Ban_Util_Suid::b64chars, $i, 1);
                Ban_Util_Suid::$ords[$char] = $i;
            }
        }
        return Ban_Util_Suid::$ords[$chr];
    }

    /**
     * Return a binary representation
     * 
     * @return string   8-byte binary representation of UUID
     */
    public function bin()
    {
        if (null === $this->_bin) {
            $ords = array();
            for ($i=0; $i<8; ++$i) {
                $ords[$i] = $this->ord($this->_str[$i]);
            }
            // <<10   <<4    >>2    <<8    <<2    >>4    <<6    <<0
            // 000000 000000 000000 000000 000000 000000 000000 000000
            // ^                 ^                  ^
            //                   <<14               <<12
            $i1 = ($ords[0]<<10) | ($ords[1]<<4) | ($ords[2]>>2);
            $i2 = ($ords[2]<<14) | ($ords[3]<<8) | ($ords[4]<<2) | ($ords[5]>>4);
            $i3 = ($ords[5]<<12) | ($ords[6]<<6) | ($ords[7]);
            $this->_bin = pack('n*', $i1, $i2, $i3);
        }
        return $this->_bin;
    }

    /**
     * Return a properly formatted UUID string
     * 
     * @return string   36-character string representation of UUID
     */
    public function __toString()
    {
        return $this->str();
    }

    public static function isSuid(&$str)
    {
        return preg_match('/^[a-zA-Z0-9._]{8}$/', $str);
    }

}
