<?php

class Ban_Response extends Zend_Controller_Response_Abstract
{
    
    public function __construct()
    {
        // dump(__class__ . '::' . __function__);
        if ($this->canSendHeaders()) {
            $this->setHeader('Allow', 'GET,HEAD,POST,PUT,PATCH,TRACE,OPTIONS', 1);
        }
    }
    
    public static function createFromZendHttpResponse(Zend_Http_Response $response)
    {
        $banResponse = new Ban_Response();
        foreach ($response->getHeaders() as $header => $value) {
            $banResponse->setHeader($header, $value);
        }
        $contentType = Ban_ContentType::fromString($response->getHeader('Content-type'));
        $banResponse->setHeader('Content-type', $contentType, true);
        $banResponse->setHttpResponseCode($response->getStatus());
        if ($contentType->isJson()) {
            try {
                $decoded = Zend_Json::decode($response->getBody());
                foreach ($decoded as $part => $content) {
                    $banResponse->setBody($content, $part);
                }
            } catch (Zend_Json_Exception $e) {
                // Do nothing
            }
        }
        return $banResponse;
    }

    protected $_contentType;

    public function setContentType($contentType)
    {
        if (is_string($contentType)) {
            $contentType = new Ban_ContentType($contentType);
        }
        $this->_contentType = $contentType;
        $this->setHeader('Content-type', $this->_contentType, true);
    }
    
    public function getContentType()
    {
        return $this->_contentType;
    }

    public function getStatus()
    {
        return $this->getHttpResponseCode();
    }

    public function isOk()
    {
        return ($this->getHttpResponseCode() == 200);
    }

    public function notExist()
    {
        return ($this->getHttpResponseCode() == 404);
    }

    public function badRequest()
    {
        return ($this->getHttpResponseCode() == 400);
    }

    /**
     * Set a header
     *
     * If $replace is true, replaces any headers already defined with that
     * $name.
     *
     * @param string $name
     * @param string $value
     * @param boolean $replace
     * @return Zend_Controller_Response_Abstract
     */
    public function setHeader($name, $value, $replace = false)
    {
        $this->canSendHeaders(true);
        $name  = $this->_normalizeHeader($name);
        $value = $value;

        if ($replace) {
            foreach ($this->_headers as $key => $header) {
                if ($name == $header['name']) {
                    unset($this->_headers[$key]);
                }
            }
        }

        $this->_headers[] = array(
            'name'    => $name,
            'value'   => $value,
            'replace' => $replace
        );

        return $this;
    }

    /**
     * Send all headers
     *
     * Sends any headers specified. If an {@link setHttpResponseCode() HTTP response code}
     * has been specified, it is sent with the first header.
     *
     * @return Zend_Controller_Response_Abstract
     */
    public function sendHeaders()
    {
        // Only check if we can send headers if we have headers to send
        if (count($this->_headersRaw) || count($this->_headers) || (200 != $this->_httpResponseCode)) {
            $this->canSendHeaders(true);
        } elseif (200 == $this->_httpResponseCode) {
            // Haven't changed the response code, and we have no headers
            return $this;
        }

        $httpCodeSent = false;

        foreach ($this->_headersRaw as $header) {
            if (!$httpCodeSent && $this->_httpResponseCode) {
                header($header, true, $this->_httpResponseCode);
                $httpCodeSent = true;
            } else {
                header($header);
            }
        }

        foreach ($this->_headers as $header) {
            if (!$httpCodeSent && $this->_httpResponseCode) {
                header($header['name'] . ': ' . (string) $header['value'], $header['replace'], $this->_httpResponseCode);
                $httpCodeSent = true;
            } else {
                header($header['name'] . ': ' . (string) $header['value'], $header['replace']);
            }
        }

        if (!$httpCodeSent) {
            header('HTTP/1.1 ' . $this->_httpResponseCode);
            $httpCodeSent = true;
        }

        return $this;
    }


    public function setType($type)
    {
        $this->_contentType->type = $type;
    }
    
    public function __set($name, $content)
    {
        return $this->setBody($content, $name);
    }
    
    public function &__get($spec)
    {
        if (array_key_exists($spec, $this->_body)) {
            if (is_array($this->_body[$spec])) {
                return $this->_body[$spec];
            }
            return $this->_body[$spec];
        }
        static $null = null;
        return $null;
    }
    
    public function __isset($spec)
    {
        return isset($this->_body[$spec]);
    }

    /**
     * Set body content
     *
     * If $name is not passed, or is not a string, resets the entire body and
     * sets the 'default' key to $content.
     *
     * If $name is a string, sets the named segment in the body array to
     * $content.
     *
     * @param mixed $content
     * @param null|string $name
     * @return Zend_Controller_Response_Abstract
     */
    public function setBody($content, $name = null)
    {
        if ((null === $name) || !is_string($name)) {
            $this->_body = array('default' => $content);
        } else {
            $this->_body[$name] = $content;
        }

        return $this;
    }


    /**
     * Return the raw body content
     *
     * @return string|array|null
     */
    public function getRawBody()
    {
        return $this->_body;
    }

    /**
     * Return the body content
     *
     * If $spec is false, returns the concatenated values of the body content
     * array. If $spec is boolean true, returns the body content array. If
     * $spec is a string and matches a named segment, returns the contents of
     * that segment; otherwise, returns null.
     *
     * @param boolean $spec
     * @return string|array|null
     */
    public function getBody($spec = false)
    {
        if (false === $spec || true === $spec) {
            return $this->_body;
        } elseif (is_string($spec) && array_key_exists($spec, $this->_body)) {
            return $this->_body[$spec];
        }
        return null;
    }
    
    /**
     * Append content to the body content
     *
     * @param string $content
     * @param null|string $name
     * @return Zend_Controller_Response_Abstract
     */
    public function appendBody($content, $name = null)
    {
        if ((null === $name) || !is_string($name)) {
            if (isset($this->_body['default'])) {
                $this->_body['default'] .= $content;
            } else {
                return $this->append('default', $content);
            }
        } elseif (isset($this->_body[$name])) {
            $this->_body[$name] .= $content;
        } else {
            return $this->append($name, $content);
        }

        return $this;
    }

    /**
     * Append a named body segment to the body content array
     *
     * If segment already exists, replaces with $content and places at end of
     * array.
     *
     * @param string $name
     * @param string $content
     * @return Zend_Controller_Response_Abstract
     */
    public function append($name, $content)
    {
        if (!is_string($name)) {
            require_once 'Zend/Controller/Response/Exception.php';
            throw new Zend_Controller_Response_Exception('Invalid body segment key ("' . gettype($name) . '")');
        }

        if (isset($this->_body[$name])) {
            unset($this->_body[$name]);
        }
        $this->_body[$name] = $content;
        return $this;
    }

    /**
     * Prepend a named body segment to the body content array
     *
     * If segment already exists, replaces with $content and places at top of
     * array.
     *
     * @param string $name
     * @param string $content
     * @return void
     */
    public function prepend($name, $content)
    {
        if (!is_string($name)) {
            require_once 'Zend/Controller/Response/Exception.php';
            throw new Zend_Controller_Response_Exception('Invalid body segment key ("' . gettype($name) . '")');
        }

        if (isset($this->_body[$name])) {
            unset($this->_body[$name]);
        }

        $new = array($name => $content);
        $this->_body = array_merge($new, $this->_body);

        return $this;
    }


    /**
     * Insert a named segment into the body content array
     *
     * @param  string $name
     * @param  string $content
     * @param  string $parent
     * @param  boolean $before Whether to insert the new segment before or
     * after the parent. Defaults to false (after)
     * @return Zend_Controller_Response_Abstract
     */
    public function insert($name, $content, $parent = null, $before = false)
    {
        if (!is_string($name)) {
            require_once 'Zend/Controller/Response/Exception.php';
            throw new Zend_Controller_Response_Exception('Invalid body segment key ("' . gettype($name) . '")');
        }

        if ((null !== $parent) && !is_string($parent)) {
            require_once 'Zend/Controller/Response/Exception.php';
            throw new Zend_Controller_Response_Exception('Invalid body segment parent key ("' . gettype($parent) . '")');
        }

        if (isset($this->_body[$name])) {
            unset($this->_body[$name]);
        }

        if ((null === $parent) || !isset($this->_body[$parent])) {
            return $this->append($name, $content);
        }

        $ins  = array($name => $content);
        $keys = array_keys($this->_body);
        $loc  = array_search($parent, $keys);
        if (!$before) {
            // Increment location if not inserting before
            ++$loc;
        }

        if (0 === $loc) {
            // If location of key is 0, we're prepending
            $this->_body = $ins + $this->_body;
        } elseif ($loc >= (count($this->_body))) {
            // If location of key is maximal, we're appending
            $this->_body = $this->_body + $ins;
        } else {
            // Otherwise, insert at location specified
            $pre  = array_slice($this->_body, 0, $loc);
            $post = array_slice($this->_body, $loc);
            $this->_body = $pre + $ins + $post;
        }

        return $this;
    }

    /**
     * Echo the body segments
     *
     * @return void
     */
    public function outputBody()
    {
        // TODO: select serializer based on content type
        echo Zend_Json::encode($this->_body);
    }

}
