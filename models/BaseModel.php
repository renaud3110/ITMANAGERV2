<?php

require_once 'config/Database.php';

class BaseModel {
    protected $db;

    public function __construct() {
        $this->db = new Database();
    }

    protected function query($sql, $params = []) {
        return $this->db->query($sql, $params);
    }

    protected function fetch($sql, $params = []) {
        return $this->db->fetch($sql, $params);
    }

    protected function fetchAll($sql, $params = []) {
        return $this->db->fetchAll($sql, $params);
    }

    protected function getLastInsertId() {
        return $this->db->lastInsertId();
    }
}
