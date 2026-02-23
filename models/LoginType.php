<?php

class LoginType extends BaseModel 
{
    public function __construct() 
    {
        parent::__construct();
    }

    public function getAll() 
    {
        $sql = "SELECT * FROM login_types ORDER BY id ASC";
        return $this->fetchAll($sql);
    }

    public function getById($id) 
    {
        $sql = "SELECT * FROM login_types WHERE id = ?";
        return $this->fetch($sql, [$id]);
    }
} 