<?php
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/security.php';

Auth::startSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// Rate limiting
if (!Security::checkRateLimit('register', 3, 600)) {
    echo json_encode(['success' => false, 'message' => 'Too many registration attempts.']);
    exit;
}

// CSRF
if (empty($input['csrf_token']) || !Security::validateCSRF($input['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request token.']);
    exit;
}

$name     = Security::sanitize($input['name'] ?? '');
$email    = Security::sanitize($input['email'] ?? '');
$password = $input['password'] ?? '';

if (!$name || !$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if (!Security::validateEmail($email)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

if (strlen($password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters.']);
    exit;
}

echo json_encode(Auth::register($name, $email, $password));
