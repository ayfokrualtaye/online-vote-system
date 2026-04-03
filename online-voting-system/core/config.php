<?php
// ============================================
// CORE CONFIGURATION
// ============================================

   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'voting_system');

define('SITE_NAME', 'VoteSecure');
define('SITE_URL', 'http://localhost/online-voting-system/public');
define('BASE_PATH', dirname(__DIR__));

// Session security
define('SESSION_LIFETIME', 3600); // 1 hour

// Security
define('HASH_SALT', 'vs_2026_secure_salt_x9k2');
define('BCRYPT_COST', 12);

// Email (configure for production)
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USER', 'noreply@votesystem.com');
define('MAIL_PASS', 'your_email_password');

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('UTC');
