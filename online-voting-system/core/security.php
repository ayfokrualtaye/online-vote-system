<?php
require_once __DIR__ . '/config.php';

class Security {

    // Hash password
    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
    }

    // Verify password
    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    // Generate anonymous user hash for vote privacy
    public static function generateUserHash(int $userId, int $electionId): string {
        return hash('sha256', $userId . HASH_SALT . $electionId . DB_NAME);
    }

    // Generate CSRF token
    public static function generateCSRF(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // Validate CSRF token
    public static function validateCSRF(string $token): bool {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    // Generate OTP
    public static function generateOTP(): string {
        return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    // Sanitize input
    public static function sanitize(string $input): string {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    // Validate email
    public static function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    // Get client IP
    public static function getIP(): string {
        $keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($keys as $key) {
            if (!empty($_SERVER[$key])) {
                return filter_var($_SERVER[$key], FILTER_VALIDATE_IP) ?: 'unknown';
            }
        }
        return 'unknown';
    }

    // Rate limiting (simple session-based)
    public static function checkRateLimit(string $action, int $maxAttempts = 5, int $window = 300): bool {
        $key = 'rate_' . $action;
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'time' => time()];
        }
        if (time() - $_SESSION[$key]['time'] > $window) {
            $_SESSION[$key] = ['count' => 0, 'time' => time()];
        }
        $_SESSION[$key]['count']++;
        return $_SESSION[$key]['count'] <= $maxAttempts;
    }

    // Generate secure token
    public static function generateToken(int $length = 32): string {
        return bin2hex(random_bytes($length));
    }
}
