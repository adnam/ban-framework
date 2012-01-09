<?php

abstract class Ban_Dao_Abstract
{
    
    /**
     * Name of DAO
     * 
     * @var string
     */
    protected $_name;

    /**
     * Which view do we use (which properties do we return in results)
     * 
     * @var string - 'default' by default
     */
    protected $_view = 'default';
    
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
    }
        
    /**
     * List of properties in this dao
     *
     * @return array
     */
    public function getProperties()
    {
		$properties = array();
        foreach ($this->model->getProperties() as $name => $prop) {
            $properties[$name] = $prop->getOptions();
        }
        return $properties;
    }
    
    /**
     * List which properties are indexed
     *
     * @return array
     */
    public function getIndexes()
    {
        return $this->model->getIndexes();
    }

    /**
     * Key primary key property name
     *
     * @return string
     */
    public function getPrimary()
    {
        return $this->model->getPrimary();
    }
    
    /**
     * List which properties are unique-keys
     *
     * @return array
     */
    public function getKeys()
    {
        return $this->model->getKeys();
    }
    
    /**
     * List which properties can be ordered
     *
     * I.e. result sets can be ordered by these columns
     *
     * @return array
     */
    public function getOrderable()
    {
        return $this->model->getOrderable();
    }

    /**
     * List which views are available
     *
     * @return array
     */
    public function getViews()
    {
        return $this->model->getViews();
    }

    /**
     * Get the name of the currently configured view
     *
     * @return string
     */
    public function getView()
    {
        return $this->_view;
    }
    
    /**
     * Set a particular view to use
     * 
     * Chainable method
     *
     * @param   <string> name of view
     * @return  <Ban_Dao_Abstract> current instance
     */
    public function setView($view)
    {
        if (!array_key_exists($view, $this->getViews())) {
            throw new Exception("Unknown view [$view]", 500);
        }
        $this->_view = $view;
        return $this;
    }
    
    /**
     * List the columns according to current configured view
     *
     * @return  <array> column names
     */
    protected function getViewColumns()
    {
        $view = $this->getView();
        $views = $this->getViews();
        if (!array_key_exists($view, $views)) {
            throw new Exception("Unknown view [$view]", 500);
        }
        return $views[$view];
    }
    
    /**
     * Set the classname used for DBTable
     *
     * Chainable method
     *
     * @param   <string> table class name
     * @return  <Ban_Dao_Abstract> current instance
     */
    public function setTableClass($tableClass)
    {
        $this->_tableClass = $tableClass;
        return $this;
    }
    
    abstract public function get($id);
    abstract public function delete($id);
    abstract function save($row);
    abstract function fetchAll();
    abstract function find($params);
    abstract function count();

}
