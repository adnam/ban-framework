<?php

abstract class Ban_Model_Abstract implements Ban_Model_Interface
{
    
    /**
     * Name of entity class
     * 
     * e.g. "user", "comment", "widget"
     *
     * @var string
     */
    protected $_name;

    /**
     * Name of a collection of these entities
     * 
     * e.g. "users", "comments", "widgets"
     * 
     * (RoR figures this out for you, but we're not that cool)
     *
     * @var string
     */
    protected $_collection;

    /**
     * Classname of DAO (data access object)
     *
     * @var string
     */
    protected $_daoClass = 'Ban_Dao_Db';
    
    /**
     * Instance of dao
     *
     * @var Ban_Dao_Interface
     */
    protected $_dao;

    /**
     * Classname of service
     *
     * @var string
     */
    protected $_serviceClass = 'Ban_Service_Generic';

    /**
     * Instance of service
     *
     * @var Ban_Service_Interface
     */
    protected $_service;

    /**
     * Properties of this entity class
     *
     * @var array
     */
    protected $_properties = array(
        'id' => null,
    );
    
    /**
     * Properties which are indexed
     *
     * @var array
     */
    protected $_indexes = array();

    /**
     * Name of property which is the primary key
     *
     * @var string
     */
    protected $_primary = 'id';

    /**
     * Names of properties which uniquely identify the entity within this model
     * 
     * @var array
     */
    protected $_keys = array('id');

    /**
     * Names of properties which may be ordered
     *
     * @var array
     */
    protected $_orderable = array();
    
    /**
     * Names of properties which are autonumeric
     *
     * @var array
     */
    protected $_auto_id = array('id');
    
    /**
     * Array of model instances
     *
     * @var array
     */
    protected static $_instances = array();
    
    /**
     * Array of routes
     *
     * @var array
     */
    protected static $_routes = array();
    
    /**
     * Model map
     *
     * @var Ban_Map
     */
    protected $_map;
    
    /**
     * Array of views for this model
     *
     * @see Ban_Model_Abstract::addView()
     * @var array
     */
    protected $_views = array();
    
    protected $_relations = array(
        'belongsTo' => array(),
        'belongsExclusive' => array(),
        'hasMany'   => array(),
        'hasOne'    => array(),
        'habtm'     => array()
    );
    
    /**
     * Constructor
     *
     * @return Ban_Model_Abstract
     */
    protected final function __construct()
    {
    }

    public static function getInstance()
    {
        $cls = get_called_class();
		if (!array_key_exists($cls, static::$_instances)) {
            static::$_instances[$cls] = new $cls();
            static::$_instances[$cls]->initProperties();
            static::$_instances[$cls]->initRelations();
            static::$_instances[$cls]->initViews();
            static::$_instances[$cls]->initDefaultViews();
            static::$_instances[$cls]->initRoutes();
        }
        return static::$_instances[$cls];
    }

    public abstract function initProperties();
    public abstract function initRelations();
    public abstract function initViews();
    public abstract function initRoutes();
    
    protected final function initDefaultViews()
    {
        if (!array_key_exists('id', $this->_views)) {
            $this->_views['id'] = array($this->_primary);
        }
        if (!array_key_exists('default', $this->_views)) {
            $this->_views['default'] = $this->getPropertyNames();
        }
    }

    public function getMap()
    {
        return $this->_map;
    }

    public function setMap(Ban_Map $map)
    {
        $this->_map = $map;
        return $this;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function getCollection()
    {
        if ($this->_collection === null) {
            return $this->getName() . 's';
        }
        return $this->_collection;
    }

    public function getDaoClass()
    {
        return $this->_daoClass;
    }
    
    public function getServiceClass()
    {
        return $this->_serviceClass;
    }
    
    public function getDao()
    {
        if ($this->_dao === null)
        {
            $daoClass = $this->getDaoClass($this);
            $this->_dao = new $daoClass($this);
        }
        return $this->_dao;
    }
    
    public function getService()
    {
        if ($this->_service === null)
        {
            $serviceClass = $this->getServiceClass();
            $this->_serviceClass = new $serviceClass($this);
        }
        return $this->_serviceClass;
    }
    
    protected function addRoute($route, $params = array())
    {
        Ban_Map::getInstance()->addRoute($this, $route, $params);
    }

    protected function addRelation($type, $model)
    {
        if (!array_key_exists($type, $this->_relations)) {
            throw new Exception("Unknown relation type [$type]", 500);
        }
        if (is_string($model)) {
            $model = $model::getInstance();
        }
        $this->$type($model);
    }

    protected function belongsTo($model)
    {
        $this->_relations['belongsTo'][$model->getName()] = $model;
    }
    
    protected function belongsExclusive($model)
    {
        $this->_relations['belongsTo'][$model->getName()] = $model;
        $this->_relations['belongsExclusive'][$model->getName()] = $model;
    }
    
    protected function hasMany($model)
    {
        $this->_relations['hasMany'][$model->getName()] = $model;
    }
    
    protected function hasOne($model)
    {
        $this->_relations['hasOne'][$model->getName()] = $model;
    }
    
    protected function habtm($model)
    {
        $this->_relations['habtm'][$model->getName()] = $model;
    }

    public function getRelations($type = null)
    {
        if ($type === null) {
            return $this->_relations;
        } elseif (!array_key_exists($type, $this->_relations)) {
            throw new Exception("Unknown relation type [$type]", 500);
        }
        return $this->_relations[$type];
    }
    
    public function getRelation($type, $name)
    {
        return $this->_relations[$type][$name];
    }
    
    public static function makeProperty($type, $options)
    {
        $cls = 'Ban_Model_Property_' . ucfirst($type);
        if (!class_exists($cls)) {
            throw new Exception("Property tyle [$cls] does not exist", 500);
        }
        $property = new $type($options);
        return $property;
    }

    protected function addProperty($name, $property, $options=array())
    {
        if (is_string($property)) {
            Ban_Model_Abstract::makeProperty($property, $options);
        }
        $this->_properties[$name] = $property;
    }

    public function getProperties()
    {
        return $this->_properties;
    }

    public function getProperty($property)
    {
        return $this->_properties[$property];
    }

    public function getPropertyNames()
    {
        return array_keys($this->_properties);
    }

    public function getIndexes()
    {
        return $this->_indexes;
    }

    protected function addIndex($property)
    {
        $this->_indexes[] = $property;
    }

    public function getPrimary()
    {
        return $this->_primary;
    }

    public function getKeys()
    {
        return $this->_keys;
    }

    protected function addKey($property)
    {
        $this->_key[] = $property;
    }

    public function getOrderable()
    {
        return $this->_orderable;
    }

    public function getViews()
    {
        return $this->_views;
    }

    protected function addView($view, $columns)
    {
        $this->_views[$view] = $column;
    }

}
