<?php
require_once __DIR__ . '/../core/database.php';

class Candidate {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getByElection(int $electionId): array {
        return $this->db->fetchAll(
            "SELECT c.*, COUNT(v.id) as vote_count 
             FROM candidates c 
             LEFT JOIN votes v ON v.candidate_id = c.id 
             WHERE c.election_id = ? 
             GROUP BY c.id ORDER BY c.name",
            'i', [$electionId]
        );
    }

    public function getById(int $id): array|null {
        return $this->db->fetchOne("SELECT * FROM candidates WHERE id = ?", 'i', [$id]);
    }

    public function create(string $name, string $party, string $bio, string $image, int $electionId): int|false {
        return $this->db->insert(
            "INSERT INTO candidates (name, party, bio, image, election_id) VALUES (?, ?, ?, ?, ?)",
            'ssssi', [$name, $party, $bio, $image, $electionId]
        );
    }

    public function update(int $id, string $name, string $party, string $bio, string $image): bool {
        $stmt = $this->db->query(
            "UPDATE candidates SET name = ?, party = ?, bio = ?, image = ? WHERE id = ?",
            'ssssi', [$name, $party, $bio, $image, $id]
        );
        return $stmt !== false;
    }

    public function delete(int $id): bool {
        $stmt = $this->db->query("DELETE FROM candidates WHERE id = ?", 'i', [$id]);
        return $stmt !== false;
    }

    public function getTotal(): int {
        $row = $this->db->fetchOne("SELECT COUNT(*) as total FROM candidates");
        return (int)($row['total'] ?? 0);
    }
}
