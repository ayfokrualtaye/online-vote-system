<?php
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../core/security.php';

class Vote {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function hasVoted(int $userId, int $electionId): bool {
        $row = $this->db->fetchOne(
            "SELECT id FROM voter_registry WHERE user_id = ? AND election_id = ?",
            'ii', [$userId, $electionId]
        );
        return $row !== null;
    }

    public function cast(int $userId, int $candidateId, int $electionId): array {
        if ($this->hasVoted($userId, $electionId)) {
            return ['success' => false, 'message' => 'You have already voted in this election.'];
        }

        $userHash = Security::generateUserHash($userId, $electionId);

        // Insert anonymous vote
        $voteId = $this->db->insert(
            "INSERT INTO votes (user_hash, candidate_id, election_id) VALUES (?, ?, ?)",
            'sii', [$userHash, $candidateId, $electionId]
        );

        if (!$voteId) {
            return ['success' => false, 'message' => 'Vote submission failed. Please try again.'];
        }

        // Mark user as voted (separate table for privacy)
        $this->db->insert(
            "INSERT INTO voter_registry (user_id, election_id) VALUES (?, ?)",
            'ii', [$userId, $electionId]
        );

        return ['success' => true, 'message' => 'Your vote has been cast successfully.'];
    }

    public function getResults(int $electionId): array {
        return $this->db->fetchAll(
            "SELECT c.id, c.name, c.party, c.image,
                    COUNT(v.id) as vote_count,
                    ROUND(COUNT(v.id) * 100.0 / NULLIF((SELECT COUNT(*) FROM votes WHERE election_id = ?), 0), 1) as percentage
             FROM candidates c
             LEFT JOIN votes v ON v.candidate_id = c.id AND v.election_id = ?
             WHERE c.election_id = ?
             GROUP BY c.id
             ORDER BY vote_count DESC",
            'iii', [$electionId, $electionId, $electionId]
        );
    }

    public function getTotalVotes(int $electionId): int {
        $row = $this->db->fetchOne(
            "SELECT COUNT(*) as total FROM votes WHERE election_id = ?", 'i', [$electionId]
        );
        return (int)($row['total'] ?? 0);
    }

    public function getTotalVotesAll(): int {
        $row = $this->db->fetchOne("SELECT COUNT(*) as total FROM votes");
        return (int)($row['total'] ?? 0);
    }
}
