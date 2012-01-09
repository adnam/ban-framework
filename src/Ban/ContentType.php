<?php
/**
 * Models a RFC 2045 content type
 *
 * RFC 2045                Internet Message Bodies            November 1996
 *
 *
 * 5.1. Syntax of the Content-Type Header Field
 *
 * In the Augmented BNF notation of RFC 822, a Content-Type header field
 * value is defined as follows:
 *
 *   content := "Content-Type" ":" type "/" subtype
 *              *(";" parameter)
 *              ; Matching of media type and subtype
 *              ; is ALWAYS case-insensitive.
 *
 *   type := discrete-type / composite-type
 *
 *   discrete-type := "text" / "image" / "audio" / "video" /
 *                    "application" / extension-token
 *
 *   composite-type := "message" / "multipart" / extension-token
 *
 *   extension-token := ietf-token / x-token
 *
 *   ietf-token := <An extension token defined by a
 *                  standards-track RFC and registered
 *                  with IANA.>
 *
 *   x-token := <The two characters "X-" or "x-" followed, with
 *               no intervening white space, by any token>
 *
 *   subtype := extension-token / iana-token
 *
 *   iana-token := <A publicly-defined extension token. Tokens
 *                  of this form must be registered with IANA
 *                  as specified in RFC 2048.>
 *
 *   parameter := attribute "=" value
 *
 *   attribute := token
 *                ; Matching of attributes
 *                ; is ALWAYS case-insensitive.
 *
 *   value := token / quoted-string
 *
 *   token := 1*<any (US-ASCII) CHAR except SPACE, CTLs,
 *               or tspecials>
 *
 *   tspecials :=  "(" / ")" / "<" / ">" / "@" /
 *                 "," / ";" / ":" / "\" / <">
 *                 "/" / "[" / "]" / "?" / "="
 *                 ; Must be in quoted-string,
 *                 ; to use within parameter values
**/
class Ban_ContentType
{
 
    const MIME_JSON = 'application/vnd.ban+json';
    const MIME_XML = 'application/vnd.ban+xml';

    const DISCRETE_TYPE = '(text|image|audio|video|application|x-[a-z]+)';
    const COMPOSITE_TYPE = '(message|multipart|x-[a-z]+)';
    const TSPECIALS =  '[()<>@",;:\\<>/\[\]?=]';
    
    protected $_type;
    protected $_subType;
    
    protected $_params = array();
    
    public function __construct($mime = Ban_ContentType::MIME_JSON, $params = array())
    {
        $this->setMime($mime);
        foreach ($params as $param => $value) {
            $this->setParam($param, $value);
        }
    }
    
    public function setParam($param, $value)
    {
        $this->_params[$param] = $value;
    }
    
    public function getParam($param)
    {
        if (array_key_exists($param, $this->_params)) {
            return $this->_params[$param];
        }
        return null;
    }

    public function __get($param)
    {
        return $this->getParam($param);
    }

    public function __set($param, $value)
    {
        return $this->setParam($param, $value);
    }

    public function getMime()
    {
        return $this->_type . '/' . $this->_subType;
    }
    
    public function setMime($mime)
    {
        list($this->_type, $this->_subType) = explode('/', $mime, 2);
    }
    
    public function __toString()
    {
        $params = array($this->getMime());
        foreach ($this->_params as $param => $value) {
            $params[] = "$param=$value";
        }
        return implode('; ', $params);
    }

    public static function fromString($ctString)
    {
        $mParts = explode(';', $ctString);
        $mime = array_shift($mParts);
        $params = array();
        if ($mParts !== null) {
            foreach ($mParts as $param) {
                $pParts = explode('=', $param, 2);
                $name = trim(array_shift($pParts));
                $val = empty($pParts)? true : trim(trim($pParts[0]), '"');
                $params[$name] = $val;
            }
        }
        return new Ban_ContentType($mime, $params);
    }
    
    public function isJson()
    {
        return substr($this->_subType, -5) == '+json';
    }
    
    public function isXml()
    {
        return substr($this->_subType, -4) == '+xml';
    }

}
