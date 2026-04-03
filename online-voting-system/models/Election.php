<?php
require_once __DIR__ . '/../core/database.php';

class Election {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(): array {
        return $this->db->fetchAll("SELECT * FROM elections ORDER BY created_at DESC");
    }

    public function getActive(): array {
        return $this->db->fetchAll("SELECT * FROM elections WHERE status = 'active' ORDER BY created_at DESC");
    }

    public function getById(int $id): array|null {
        return $this->db->fetchOne("SELECT * FROM elections WHERE id = ?", 'i', [$id]);
    }

    public function create(string $title, string $description, string $status, string $start, string $end): int|false {
        return $this->db->insert(
            "INSERT INTO elections (title, description, status, start_date, end_date) VALUES (?, ?, ?, ?, ?)",
            'sssss', [$title, $description, $status, $start, $end]
        );
    }

    public function update(int $id, string $title, string $description, string $status, string $start, string $end): bool {
        $stmt = $this->db->query(
            "UPDATE elections SET title = ?, description = ?, status = ?, start_date = ?, end_date = ? WHERE id = ?",
            'sssssi', [$title, $description, $status, $start, $end, $id]
        );
        return $stmt !== false;
    }

    public function delete(int $id): bool {
        $stmt = $this->db->query("DELETE FROM elections WHERE id = ?", 'i', [$id]);
        return $stmt !== false;
    }

    public function getTotal(): int {
        $row = $this->db->fetchOne("SELECT COUNT(*) as total FROM elections");
        return (int)($row['total'] ?? 0);
    }
}
