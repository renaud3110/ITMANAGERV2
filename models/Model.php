<?php

class Model extends BaseModel 
{
    public function __construct() 
    {
        parent::__construct();
    }

    public function getAll() 
    {
        $sql = "SELECT m.*, mf.name as manufacturer_name 
                FROM models m 
                LEFT JOIN manufacturers mf ON m.manufacturer_id = mf.id 
                ORDER BY mf.name ASC, m.name ASC";
        return $this->fetchAll($sql);
    }

    public function getById($id) 
    {
        $sql = "SELECT * FROM models WHERE id = ?";
        return $this->fetch($sql, [$id]);
    }
} 