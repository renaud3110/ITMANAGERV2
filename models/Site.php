<?php

require_once 'models/BaseModel.php';

class Site extends BaseModel {

    public function getAllSites() {
        $sql = "SELECT s.*, t.name as tenant_name
                FROM sites s
                LEFT JOIN tenants t ON s.tenant_id = t.id
                ORDER BY t.name, s.name";
        return $this->fetchAll($sql, []);
    }

    public function getSiteById($id) {
        $sql = "SELECT s.*, t.name as tenant_name
                FROM sites s
                LEFT JOIN tenants t ON s.tenant_id = t.id
                WHERE s.id = ?";
        return $this->fetch($sql, [$id]);
    }

    public function getSitesByTenant($tenantId) {
        if ($tenantId === 'all') {
            return $this->getAllSites();
        }

        $sql = "SELECT s.*, t.name as tenant_name
                FROM sites s
                LEFT JOIN tenants t ON s.tenant_id = t.id
                WHERE s.tenant_id = ?
                ORDER BY s.name";
        return $this->fetchAll($sql, [$tenantId]);
    }

    public function createSite($data) {
        $sql = "INSERT INTO sites (name, address, tenant_id, is_default, created_at) VALUES (?, ?, ?, ?, NOW())";
        return $this->query($sql, [
            $data['name'],
            $data['address'] ?? '',
            $data['tenant_id'],
            $data['is_default'] ?? 0
        ]);
    }

    public function updateSite($id, $data) {
        $sql = "UPDATE sites SET name = ?, address = ?, tenant_id = ?, is_default = ? WHERE id = ?";
        return $this->query($sql, [
            $data['name'],
            $data['address'] ?? '',
            $data['tenant_id'],
            $data['is_default'] ?? 0,
            $id
        ]);
    }

    public function deleteSite($id) {
        return $this->query("DELETE FROM sites WHERE id = ?", [$id]);
    }

    public function getSitesForDropdown($tenantId = null) {
        if ($tenantId === 'all' || $tenantId === null) {
            $sql = "SELECT s.id, s.name, t.name as tenant_name
                    FROM sites s
                    LEFT JOIN tenants t ON s.tenant_id = t.id
                    ORDER BY t.name, s.name";
            return $this->fetchAll($sql, []);
        } else {
            $sql = "SELECT id, name FROM sites WHERE tenant_id = ? ORDER BY name";
            return $this->fetchAll($sql, [$tenantId]);
        }
    }
}
