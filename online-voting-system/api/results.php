<?php
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../models/Vote.php';

Auth::startSession();

if (!Auth::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

$electionId = (int)($_GET['election_id'] ?? 0);
if (!$electionId) {
    echo json_encode(['success' => false, 'message' => 'Election ID required.']);
    exit;
}

$voteModel = new Vote();
$results   = $voteModel->getResults($electionId);
$total     = $voteModel->getTotalVotes($electionId);

echo json_encode(['success' => true, 'results' => $results, 'total' => $total]);
