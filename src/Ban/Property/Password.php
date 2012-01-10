<?php

class Ban_Property_Password extends Ban_Property_String
{

    protected $_options = array(
        'index' => false,
        'primary' => false,
        'salt_length' => 9,
    );
    
    public function validate(&$value)
    {
        return is_string($value);
    }

    public function getStorageValue(&$value)
    {
        return $this->hash($value);
    }

    /**
     * Check a password against a given salted-hash
     *
     * The saltedHash is expected to be in the format described 
     * in Plio_Password::saltHash(), see below.
     *
     * @param string password
     * @param string salted hash
     * @return boolean TRUE if matches, false otherwise
     */
    public function match($password, $saltedHash)
    {
        $parts = explode(':', $saltedHash, 2);
        if (count($parts) < 2) {
            // No salt found in the password
            return false;
        }
        list($salt_base64, $hash) = $parts;
        $salt = base64_decode($salt_base64);
        return $hash == sha1($salt.$password);
    }
    
    /**
     * Generate a random string of a given length
     * The string contains printable ASCII characters (in range 0x20 - 0x7E)
     *
     * @param int length of salt, defaults to 'salt_length' option if NULL.
     * @return string salty string
     */
    public function makeSalt($length = null)
    {
        if ($length === null || !is_int($length)) {
            $length = $this->getOption('salt_length');
        }
        $salt = '';
        for ($i = 0; $i<$length; ++$i) {
            $salt .= chr(mt_rand(0x20, 0x7E));
        }
        return $salt;
    }
    
    /**
     * Return a salted hash of a string
     *
     * Uses SHA-1 to do the hashing.
     * 
     * If no salt given, generates a 9-character, ascii-printable, random salt
     * 
     * Returns the hash in this format:
     * 
     *   XXXXXXXXXXXX:YYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY
     *   ^            ^ SHA1(CONCAT(salt, string))
     *   BASE64_ENCODE(salt)
     *   
     * @param <string>      string to be hashed
     * @param <string>      salt (optional)
     * @return <string>     hashed string
     */
    public function hash($string, $salt=null)
    {
        if ($salt===null) {
            $salt = $this->makeSalt();
        }
        $hash = sha1($salt . $string);
        return base64_encode($salt) . ':' . $hash;
    }

}
