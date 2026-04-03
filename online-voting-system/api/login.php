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
if (!Security::checkRateLimit('login', 5, 300)) {
    echo json_encode(['success' => false, 'message' => 'Too many attempts. Try again in 5 minutes.']);
    exit;
}

// CSRF validation
if (empty($input['csrf_token']) || !Security::validateCSRF($input['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request token.']);
    exit;
}

$email    = Security::sanitize($input['email'] ?? '');
$password = $input['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
    exit;
}

$result = Auth::login($email, $password);

if ($result['success']) {
    $redirect = $result['role'] === 'admin'
        ? '/online-voting-system/views/admin/dashboard.php'
        : '/online-voting-system/views/voter/dashboard.php';
    echo json_encode(['success' => true, 'redirect' => $redirect]);
} else {
    echo json_encode($result);
}
