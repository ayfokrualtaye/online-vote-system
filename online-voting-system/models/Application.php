<?php
require_once __DIR__ . '/../core/database.php';

class Application {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function apply(int $userId, int $electionId, string $party, string $bio, string $manifesto, string $image = ''): array {
        // Check already applied
        $existing = $this->db->fetchOne(
            "SELECT id, status FROM candidate_applications WHERE user_id = ? AND election_id = ?",
            'ii', [$userId, $electionId]
        );
        if ($existing) {
            return ['success' => false, 'message' => 'You have already applied for this election. Status: ' . ucfirst($existing['status'])];
        }

        $id = $this->db->insert(
            "INSERT INTO candidate_applications (user_id, election_id, party, bio, manifesto, image) VALUES (?, ?, ?, ?, ?, ?)",
            'iissss', [$userId, $electionId, $party, $bio, $manifesto, $image]
        );

        return $id
            ? ['success' => true,  'message' => 'Application submitted! Awaiting admin approval.']
            : ['success' => false, 'message' => 'Submission failed. Please try again.'];
    }

    public function getByUser(int $userId): array {
        return $this->db->fetchAll(
            "SELECT a.*, e.title as election_title, e.status as election_status
             FROM candidate_applications a
             JOIN elections e ON e.id = a.election_id
             WHERE a.user_id = ?
             ORDER BY a.applied_at DESC",
            'i', [$userId]
        );
    }

    public function getPending(): array {
        return $this->db->fetchAll(
            "SELECT a.*, u.name as user_name, u.email as user_email, e.title as election_title
             FROM candidate_applications a
             JOIN users u ON u.id = a.user_id
             JOIN elections e ON e.id = a.election_id
             WHERE a.status = 'pending'
             ORDER BY a.applied_at DESC"
        );
    }

    public function getAll(): array {
        return $this->db->fetchAll(
            "SELECT a.*, u.name as user_name, u.email as user_email, e.title as election_title
             FROM candidate_applications a
             JOIN users u ON u.id = a.user_id
             JOIN elections e ON e.id = a.election_id
             ORDER BY a.applied_at DESC"
        );
    }

    public function approve(int $appId, string $note = ''): bool {
        $app = $this->db->fetchOne("SELECT * FROM candidate_applications WHERE id = ?", 'i', [$appId]);
        if (!$app) return false;

        // Update application status
        $this->db->query(
            "UPDATE candidate_applications SET status = 'approved', admin_note = ?, reviewed_at = NOW() WHERE id = ?",
            'si', [$note, $appId]
        );

        // Get user name
        $user = $this->db->fetchOne("SELECT name FROM users WHERE id = ?", 'i', [$app['user_id']]);

        // Add to candidates table
        $this->db->insert(
            "INSERT INTO candidates (name, party, bio, image, election_id, user_id, approval_status, manifesto)
             VALUES (?, ?, ?, ?, ?, ?, 'approved', ?)",
            'ssssiis', [
                $user['name'],
                $app['party'],
                $app['bio'],
                $app['image'] ?: 'https://images.unsplash.com/photo-1633332755192-727a05c4013d?w=200&h=200&fit=crop&crop=face',
                $app['election_id'],
                $app['user_id'],
                $app['manifesto']
            ]
        );

        return true;
    }

    public function reject(int $appId, string $note = ''): bool {
        $stmt = $this->db->query(
            "UPDATE candidate_applications SET status = 'rejected', admin_note = ?, reviewed_at = NOW() WHERE id = ?",
            'si', [$note, $appId]
        );
        return $stmt !== false;
    }

    public function countPending(): int {
        $row = $this->db->fetchOne("SELECT COUNT(*) as total FROM candidate_applications WHERE status = 'pending'");
        return (int)($row['total'] ?? 0);
    }
}
