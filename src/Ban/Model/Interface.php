<?php

interface Ban_Model_Interface
{
    /**
     * Get a singleton instance of this model
     *
     * @return Ban_Model_Interface
     */
    static function getInstance();

    /**
     * Get the name of this model
     *
     * @return string
     */
    function getName();
    
    /**
     * Get the collection name of this model 
     * Should be the plural form of getName()
     *
     * @return string
     */
    function getCollection();

    /**
     * Get the classname of the DAO (data access object) for this model
     *
     * @return string
     */
    function getDaoClass();
    
    /**
     * Get the classname of the service for this model
     *
     * @return string
     */
    function getServiceClass();
    
    /**
     * Get an instance of the DAO for this model
     *
     * @return Ban_Dao_Interface
     */
    function getDao();
    
    /**
     * Get an instance of the service for this model
     *
     * @return Ban_Service_Interface
     */
    function getService();
    
    /**
     * Get a list of relations for a given type
     *
     * @return array
     */
    function getRelations($type);
    
    /**
     * Get a particular relation
     *
     * @return Ban_Model
     */
    function getRelation($type, $name);

    /**
     * Get a list of properties for this model
     *
     * @return array
     */
    function getProperties();
    
    /**
     * Get a list of names for properties of this 
     *
     * @return array
     */
    function getPropertyNames();

    /**
     * Get a list of property names which are indexed
     *
     * @return array
     */
    function getIndexes();

    /**
     * Get the name of the proprty which is its 'primary' identifier
     *
     * @return string
     */
    function getPrimary();

    /**
     * Get a list of property names which are unique identifiers
     *
     * @return array
     */
    function getKeys();

    /**
     * Get a list of property names which are orderable
     *
     * @return array
     */
    function getOrderable();

    /**
     * Get a list of views availabe for this model
     *
     * @return array
     */
    function getViews();
}
