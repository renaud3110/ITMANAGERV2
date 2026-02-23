<?php

class User extends BaseModel {

    public function getAllUsers() {
        return $this->fetchAll("SELECT * FROM users ORDER BY name");
    }

    public function getUserById($id) {
        return $this->fetch("SELECT * FROM users WHERE id = ?", [$id]);
    }

    public function getUserByEmail($email) {
        return $this->fetch("SELECT * FROM users WHERE email = ?", [$email]);
    }

    public function createUser($data) {
        $sql = "INSERT INTO users (name, email, password, tenant_id, is_global_admin, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
        return $this->query($sql, [
            $data['name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['tenant_id'] ?? null,
            $data['is_global_admin'] ?? 0
        ]);
    }

    public function updateUser($id, $data) {
        $sql = "UPDATE users SET name = ?, email = ?, tenant_id = ?, is_global_admin = ? WHERE id = ?";
        return $this->query($sql, [
            $data['name'],
            $data['email'],
            $data['tenant_id'] ?? null,
            $data['is_global_admin'] ?? 0,
            $id
        ]);
    }

    public function deleteUser($id) {
        return $this->query("DELETE FROM users WHERE id = ?", [$id]);
    }

    public function authenticate($email, $password) {
        $user = $this->getUserByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    public function getUsersWithTenant() {
        $sql = "SELECT u.*, t.name as tenant_name
                FROM users u
                LEFT JOIN tenants t ON u.tenant_id = t.id
                ORDER BY u.name";
        return $this->fetchAll($sql);
    }
}
