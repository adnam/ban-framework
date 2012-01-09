<?php

interface Ban_Dao_Interface
{
    public function get($id);
    public function delete($id);
    public function save($row);
    public function fetchAll();
    public function find($params);
    public function count();
}
