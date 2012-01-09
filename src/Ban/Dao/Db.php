<?php

class Ban_Dao_Db extends Ban_Dao_Abstract
{

    /**
     * Classname of db table
     * 
     * @var string
     */
    protected $_tableClass = 'Zend_Db_Table';

    /**
     * Database table instance
     * @var Zend_Db_Table_Abstract
     */
    protected $_dbTable;
    
    /**
     * Set the database table
     *
     * Chainable method
     *
     * @return  <Ban_Dao_Abstract> current instance
     */
    public function setDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable($this->model->getCollection());
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }
    
    /**
     * Get the database table
     *
     * @return  <Zend_Db_Table_Abstract>
     */
    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable($this->_tableClass);
        }
        return $this->_dbTable;
    }

    public function get($id)
    {
        $result = $this->getDbTable()->find($id)->current();
        if ($result === null) {
            $name = $this->model->getName();
            throw new Exception("Entity [$name] with id [$id] does not exist", 404);
        }
        return $result->toArray();
    }

    public function fetchAll($where = null, $limit=10, $page=0, $order=null, $orderDir='ASC')
    {
        $table = $this->getDbTable();
        $columns = $this->getViewColumns();
        $select = $table->select()->from($table, $columns);
        if (!empty($where)) {
            foreach($where as $col => $val) {
                if (!in_array($col, $table->info('cols'))) {
                    throw new Ban_Exception_Client("Unknown property [$col]", 400);
                }
                if (isset($val[0]) && $val[0] === '~') {
                    $select->where("$col LIKE ?", substr($val, 1));
                } else {
                    $select->where("$col = ?", $val);
                }
            }
        }
        if (!empty($order)) {
            $select->order($order);
        }
        $select->limit($limit, $page*$limit);
        // dump($select->assemble());
        $resultSet = $this->getDbTable()->fetchAll($select);
        return $resultSet->toArray();
    }
    
    public function save($row)
    {
        if (!isset($row[$this->getPrimary()])) {
            $idField = $this->model->getProperty($this->getPrimary());
            if ($idField instanceof Ban_Property_Uuid) {
                $row['id'] = $idField->gen();
            }
            $result = $this->getDbTable()->insert($row);
        } else {
            $result = $this->getDbTable()->update($row, array('id = ?' => $id));
        }
        return $result;
    }
    
    public function delete($where)
    {
        if (is_int($where) || is_string($where)) {
            $where = array('id = ?' => $where);
        }
        return $this->getDbTable()->delete($where);
    }
    
    public function find($where)
    {
        $result = $this->getDbTable()->fetchAll($where);
        $entries   = array();
        $class = $this->_modelClass;
        foreach ($result as $row) {
            $class = $this->_modelClass;
            $entries[] = new $class($row->toArray());
        }
        return $entries;
    }

    public function count($filter = null)
    {
        $table = $this->getDbTable();
        $select = $table->select()->from($table, array('count(*) as cnt'));
        if (!empty($where)) {
            foreach($filter as $col => $val) {
                if (!in_array($col, $table->info('cols'))) {
                    continue;
                }
                if (isset($val[0]) && $val[0] === '~') {
                    $select->filter("$col LIKE ?", substr($val, 1));
                } else {
                    $select->filter("$col = ?", $val);
                }
            }
        }
        $rows = $table->fetchAll($select);
        return($rows[0]->cnt);
    }

}
