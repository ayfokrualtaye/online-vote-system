<?php
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/security.php';
require_once __DIR__ . '/../models/Vote.php';
require_once __DIR__ . '/../models/Election.php';

Auth::startSession();

if (!Auth::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// CSRF
if (empty($input['csrf_token']) || !Security::validateCSRF($input['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request token.']);
    exit;
}

$candidateId = (int)($input['candidate_id'] ?? 0);
$electionId  = (int)($input['election_id'] ?? 0);
$userId      = (int)$_SESSION['user_id'];

if (!$candidateId || !$electionId) {
    echo json_encode(['success' => false, 'message' => 'Invalid vote data.']);
    exit;
}

// Verify election is active
$election = (new Election())->getById($electionId);
if (!$election || $election['status'] !== 'active') {
    echo json_encode(['success' => false, 'message' => 'This election is not currently active.']);
    exit;
}

// Cast vote
$voteModel = new Vote();
$result    = $voteModel->cast($userId, $candidateId, $electionId);

// Log activity
Database::getInstance()->query(
    "INSERT INTO activity_logs (user_id, action, ip_address) VALUES (?, ?, ?)",
    'iss', [$userId, 'vote_cast:election_' . $electionId, Security::getIP()]
);

echo json_encode($result);
