<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/security.php';

class Auth {
    private static Database $db;

    private static function db(): Database {
        if (!isset(self::$db)) self::$db = Database::getInstance();
        return self::$db;
    }

    public static function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path'     => '/',
                'secure'   => false, // set true in production (HTTPS)
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            session_start();
        }
    }

    public static function login(string $email, string $password): array {
        $user = self::db()->fetchOne(
            "SELECT * FROM users WHERE email = ? LIMIT 1",
            's', [$email]
        );

        if (!$user || !Security::verifyPassword($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }

        if (!$user['verified']) {
            return ['success' => false, 'message' => 'Please verify your email first.'];
        }

        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;

        self::log($user['id'], 'login');

        return ['success' => true, 'role' => $user['role']];
    }

    public static function register(string $name, string $email, string $password): array {
        $existing = self::db()->fetchOne("SELECT id FROM users WHERE email = ?", 's', [$email]);
        if ($existing) {
            return ['success' => false, 'message' => 'Email already registered.'];
        }

        $hashed = Security::hashPassword($password);
        $otp    = Security::generateOTP();
        $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        $id = self::db()->insert(
            "INSERT INTO users (name, email, password, otp_code, otp_expires) VALUES (?, ?, ?, ?, ?)",
            'sssss', [$name, $email, $hashed, $otp, $expiry]
        );

        if (!$id) return ['success' => false, 'message' => 'Registration failed. Try again.'];

        // In production: send OTP via email
        // For demo: auto-verify
        self::db()->query("UPDATE users SET verified = 1 WHERE id = ?", 'i', [$id]);

        return ['success' => true, 'message' => 'Registration successful. You can now log in.', 'otp' => $otp];
    }

    public static function logout(): void {
        self::startSession();
        if (isset($_SESSION['user_id'])) {
            self::log($_SESSION['user_id'], 'logout');
        }
        session_destroy();
    }

    public static function isLoggedIn(): bool {
        return !empty($_SESSION['logged_in']) && !empty($_SESSION['user_id']);
    }

    public static function isAdmin(): bool {
        return self::isLoggedIn() && $_SESSION['user_role'] === 'admin';
    }

    public static function requireLogin(string $redirect = '../public/login.php'): void {
        if (!self::isLoggedIn()) {
            header("Location: $redirect");
            exit;
        }
    }

    public static function requireAdmin(string $redirect = '../public/login.php'): void {
        if (!self::isAdmin()) {
            header("Location: $redirect");
            exit;
        }
    }

    public static function currentUser(): array|null {
        if (!self::isLoggedIn()) return null;
        return self::db()->fetchOne("SELECT id, name, email, role FROM users WHERE id = ?", 'i', [$_SESSION['user_id']]);
    }

    private static function log(int $userId, string $action): void {
        self::db()->query(
            "INSERT INTO activity_logs (user_id, action, ip_address, user_agent) VALUES (?, ?, ?, ?)",
            'isss', [$userId, $action, Security::getIP(), $_SERVER['HTTP_USER_AGENT'] ?? '']
        );
    }
}
