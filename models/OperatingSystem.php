<?php

class OperatingSystem extends BaseModel 
{
    public function __construct() 
    {
        parent::__construct();
    }

    public function getAll() 
    {
        $sql = "SELECT * FROM operating_systems ORDER BY name ASC";
        return $this->fetchAll($sql);
    }

    public function getById($id) 
    {
        $sql = "SELECT * FROM operating_systems WHERE id = ?";
        return $this->fetch($sql, [$id]);
    }
} 