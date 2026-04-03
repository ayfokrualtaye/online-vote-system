<?php
require_once __DIR__ . '/../core/database.php';

class User {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(): array {
        return $this->db->fetchAll("SELECT id, name, email, role, verified, created_at FROM users ORDER BY created_at DESC");
    }

    public function getById(int $id): array|null {
        return $this->db->fetchOne("SELECT id, name, email, role, verified, created_at FROM users WHERE id = ?", 'i', [$id]);
    }

    public function update(int $id, string $name, string $email): bool {
        $stmt = $this->db->query("UPDATE users SET name = ?, email = ? WHERE id = ?", 'ssi', [$name, $email, $id]);
        return $stmt !== false;
    }

    public function delete(int $id): bool {
        $stmt = $this->db->query("DELETE FROM users WHERE id = ? AND role != 'admin'", 'i', [$id]);
        return $stmt !== false;
    }

    public function getTotalVoters(): int {
        $row = $this->db->fetchOne("SELECT COUNT(*) as total FROM users WHERE role = 'voter'");
        return (int)($row['total'] ?? 0);
    }
}
