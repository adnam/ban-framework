<?php

class Ban_Route
{
    protected $_model;
    
    protected $_subroutes = array();

    protected $_params = array();

    public function __construct($route, $params, $model = null)
    {
        if (is_string($route)) {
            $route = trim($route);
        }
        $this->addRoute($route, $params, $model);
    }

    /**
     * Trim white-space and forward-slashes from the ends of a route string
     * 
     * Trims the following characters:
     *      "/"     (ASCII 46 (0x2F)), a forward slash
     *      " "     (ASCII 32 (0x20)), an ordinary space.
     *      "\t"    (ASCII 09 (0x09)), a tab.
     *      "\n"    (ASCII 10 (0x0A)), a new line (line feed).
     *      "\r"    (ASCII 13 (0x0D)), a carriage return.
     *      "\0"    (ASCII 00 (0x00)), the NUL-byte.
     *      "\x0B"  (ASCII 11 (0x0B)), a vertical tab.
     *
     * @param string route
     * @return string route
     */
    public function trim($route)
    {
        return trim($route, "\x2F\x20\x09\x0A\x0D\x00\x0B");
    }

    public function getModel()
    {
        return $this->_model;
    }
    
    public function getParams()
    {
        return $this->_params;
    }

    public function getRoutes()
    {
        return $this->_subroutes;
    }

    public function getRoute($name)
    {
        return $this->_subroutes[$name];
    }
    
    public function hasSubRoute($name)
    {
        return array_key_exists($name, $this->_subroutes);
    }

    public function addRoute($route, $params, $model)
    {
        if (is_string($route)) {
            $route = $this->trim($route);
            if (empty($route)) {
                $route = array();
            } else {
                $route = explode('/', $route);
            }
        }
        #dd("Adding route (".count($route).") [" . implode("/", $route) . "] for model [" . get_class($model) . "]");
        if (count($route) === 0) {
            if ($this->_model instanceof Ban_Model_Interface) {
                throw new Ban_Exception(
                    "The models [" . $model->getName() . "] and [" 
                    . $this->_model->getName() . "] cannot have the same route",
                    500
                );
            }
            $this->_params = $params;
            $this->_model = $model;
        } else {
            $first = array_shift($route);
            if ($this->hasSubRoute($first)) {
                $this->getRoute($first)->addRoute($route, $params, $model);
            } else {
                $this->_subroutes[$first] = new Ban_Route($route, $params, $model);
            }
        }
    }

    /**
     * Combine the passed URL parameters with the assigned
     * pasrater names, producing an associative array.
     *
     * @param array Found URL params
     * @return array associative array of combined parameters
     */
    public function combineParams($params)
    {
        if (empty($this->_params)) {
            return $this->_params;
        }
        $params = array_pad($params, count($this->_params), null);
        return array_combine($this->_params, $params);
    }

    public function match($uri, $params = array())
    {
        if (!is_array($uri)) {
            $uri = $this->trim($uri);
            if (!empty($uri)) {
                $uri = explode('/', $uri);
            }
        }
        // dd("Trying to match [" . implode("/", $uri) . "] against [" . implode(', ', array_keys($this->_subroutes)) . "]");
        if (empty($uri)) {
            return array($this->_model, $this->combineParams($params));
        }
        $first = array_shift($uri);
        if (array_key_exists($first, $this->_subroutes)) {
            return $this->_subroutes[$first]->match($uri, $params);
        } elseif (array_key_exists('*', $this->_subroutes)) {
            $params[] = $first;
            return $this->_subroutes['*']->match($uri, $params);
        }
        return array(null, null);
    }

    public function toArray()
    {
        $asArray = array(
            'subroutes' => array(),
            'model' => ($this->_model instanceof Ban_Model_Interface)? $this->_model->getName() : null,
            'params' => $this->_params
        );
        foreach ($this->_subroutes as $name => $route) {
            $asArray['subroutes'][$name] = $route->toArray();
        }
        return $asArray;
    }
    
    public function flatten($base = null)
    {
        $flat = array();
        if ($this->_model instanceof Ban_Model_Interface) {
            $flat[] = array(
                'uri' => $base,
                'model' => $this->_model->getName(),
                'params' => $this->_params
            );
        }
        foreach ($this->_subroutes as $name => $route) {
            $flat = array_merge($flat, $route->flatten($base.'/'.$name));
        }
        return $flat;
    }

}
