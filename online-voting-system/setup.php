<?php
// ============================================
// SETUP SCRIPT — Run once to initialize DB
// Visit: http://localhost/online-voting-system/setup.php
// ============================================

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'voting_system';

$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die('<div style="font-family:monospace;color:red;padding:20px;">
        ❌ Cannot connect to MySQL: ' . $conn->connect_error . '<br><br>
        Make sure MySQL is running in XAMPP Control Panel.
    </div>');
}

$errors = [];
$done   = [];

// Create database
$conn->query("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db($db);
$done[] = "✅ Database '$db' ready";

// Create tables
$tables = [
"CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','voter') DEFAULT 'voter',
    verified TINYINT(1) DEFAULT 0,
    otp_code VARCHAR(10) DEFAULT NULL,
    otp_expires DATETIME DEFAULT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",
"CREATE TABLE IF NOT EXISTS elections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    status ENUM('upcoming','active','closed') DEFAULT 'upcoming',
    start_date DATETIME,
    end_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",
"CREATE TABLE IF NOT EXISTS candidates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    party VARCHAR(100),
    bio TEXT,
    image VARCHAR(255) DEFAULT 'default.png',
    election_id INT NOT NULL,
    user_id INT DEFAULT NULL,
    approval_status ENUM('pending','approved','rejected') DEFAULT 'approved',
    manifesto TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)",
"CREATE TABLE IF NOT EXISTS candidate_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    election_id INT NOT NULL,
    party VARCHAR(100),
    bio TEXT,
    manifesto TEXT,
    image VARCHAR(255) DEFAULT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    admin_note TEXT DEFAULT NULL,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at DATETIME DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE,
    UNIQUE KEY unique_application (user_id, election_id)
)",
"CREATE TABLE IF NOT EXISTS votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_hash VARCHAR(64) NOT NULL,
    candidate_id INT NOT NULL,
    election_id INT NOT NULL,
    voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_vote (user_hash, election_id),
    FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE
)",
"CREATE TABLE IF NOT EXISTS voter_registry (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    election_id INT NOT NULL,
    voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_voter (user_id, election_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE
)",
"CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    action VARCHAR(255),
    details TEXT DEFAULT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)"
];

foreach ($tables as $sql) {
    if (!$conn->query($sql)) {
        $errors[] = "❌ Table error: " . $conn->error;
    }
}
$done[] = "✅ All tables created";

// Admin user — generate hash at runtime (no $ escaping issues)
$adminPassword = 'Admin@123';
$adminHash     = password_hash($adminPassword, PASSWORD_BCRYPT, ['cost' => 12]);

$stmt = $conn->prepare("INSERT IGNORE INTO users (name, email, password, role, verified) VALUES (?, ?, ?, 'admin', 1)");
$stmt->bind_param('sss', $name, $email, $adminHash);
$name  = 'Administrator';
$email = 'admin@votesystem.com';
$stmt->execute();
$done[] = "✅ Admin user ready (admin@votesystem.com / Admin@123)";

// Elections
$electionsExist = $conn->query("SELECT COUNT(*) as c FROM elections")->fetch_assoc()['c'];
if ($electionsExist == 0) {
    $elections = [
        ['National Election 2026', 'Vote for your preferred presidential candidate for the term 2026-2030. Shape the future of the nation.', 'active', '2026-01-01', '2026-12-31'],
        ['City Council Election', 'Elect your local city council representatives who will manage city infrastructure, budget, and community programs.', 'active', '2026-03-01', '2026-06-30'],
        ['Women Leadership Election', 'Empowering women in governance — vote for the next women leadership council members driving equality and progress.', 'active', '2026-04-01', '2026-08-31'],
        ['Student Union Election', 'Cast your vote for student union president and representatives for the academic year 2026-2027.', 'upcoming', '2026-06-01', '2026-07-15'],
        ['Community Board Election', 'Vote for community board members who will represent your neighborhood in local governance decisions.', 'upcoming', '2026-07-01', '2026-09-30'],
        ['Tech Innovation Council', 'Elect the technology and innovation council that will guide digital transformation and smart city initiatives.', 'active', '2026-02-01', '2026-05-31'],
        ['Environmental Council Election', 'Choose your environmental council representatives committed to climate action and sustainable development.', 'active', '2026-03-15', '2026-07-31'],
    ];
    $stmt = $conn->prepare("INSERT INTO elections (title, description, status, start_date, end_date) VALUES (?,?,?,?,?)");
    foreach ($elections as $e) {
        $stmt->bind_param('sssss', $e[0], $e[1], $e[2], $e[3], $e[4]);
        $stmt->execute();
    }
    $done[] = "✅ " . count($elections) . " elections inserted";
} else {
    $done[] = "ℹ️ Elections already exist — skipped";
}

// Candidates
$candidatesExist = $conn->query("SELECT COUNT(*) as c FROM candidates")->fetch_assoc()['c'];
if ($candidatesExist == 0) {
    $candidates = [
        // Election 1 - National
        ['Alexandra Rivera', 'Progressive Party', 'Former senator with 15 years of public service experience focused on healthcare and education reform.', 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=200&h=200&fit=crop&crop=face', 1],
        ['Marcus Thompson', 'Liberty Alliance', 'Business leader and community advocate focused on economic growth and job creation.', 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=200&h=200&fit=crop&crop=face', 1],
        ['Sarah Chen', 'United Front', 'Environmental scientist and policy expert committed to a sustainable and green future.', 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=200&h=200&fit=crop&crop=face', 1],
        ['James Okafor', 'People First', 'Human rights lawyer and activist with a vision for social justice and equality.', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&h=200&fit=crop&crop=face', 1],
        // Election 2 - City Council
        ['Maria Santos', 'City Progress', 'Urban planner with 10 years experience improving city infrastructure and public transport.', 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=200&h=200&fit=crop&crop=face', 2],
        ['David Kim', 'Community First', 'Local business owner committed to supporting small businesses and neighborhood development.', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=200&h=200&fit=crop&crop=face', 2],
        ['Priya Patel', 'Green City', 'Environmental engineer advocating for clean energy and sustainable city planning.', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&h=200&fit=crop&crop=face', 2],
        // Election 3 - Women Leadership
        ['Dr. Amina Hassan', 'Equality Now', 'Medical doctor and women rights advocate with 20 years of community service.', 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=200&h=200&fit=crop&crop=face', 3],
        ['Linda Osei', 'Women United', 'Entrepreneur and mentor who has helped over 500 women start their own businesses.', 'https://images.unsplash.com/photo-1489424731084-a5d8b219a5bb?w=200&h=200&fit=crop&crop=face', 3],
        ['Rachel Nguyen', 'Future Leaders', 'Tech executive and STEM education advocate empowering the next generation of women leaders.', 'https://images.unsplash.com/photo-1508214751196-bcfd4ca60f91?w=200&h=200&fit=crop&crop=face', 3],
        // Election 4 - Student Union
        ['Ethan Brooks', 'Student Voice', 'Computer science student and campus activist pushing for better academic resources.', 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=200&h=200&fit=crop&crop=face', 4],
        ['Zoe Williams', 'Campus United', 'Psychology student focused on mental health awareness and student welfare programs.', 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=200&h=200&fit=crop&crop=face', 4],
        ['Amir Khalil', 'New Generation', 'Engineering student and innovation club president with plans to modernize campus facilities.', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=200&h=200&fit=crop&crop=face', 4],
        // Election 5 - Community Board
        ['Grace Adeyemi', 'Neighborhood First', 'Social worker dedicated to improving community services and youth programs.', 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=200&h=200&fit=crop&crop=face', 5],
        ['Tom Reeves', 'Local Roots', 'Retired teacher with 30 years of community involvement and local governance experience.', 'https://images.unsplash.com/photo-1566492031773-4f4e44671857?w=200&h=200&fit=crop&crop=face', 5],
        ['Nina Castillo', 'Community Forward', 'Urban developer focused on affordable housing and green public spaces.', 'https://images.unsplash.com/photo-1508214751196-bcfd4ca60f91?w=200&h=200&fit=crop&crop=face', 5],
        // Election 6 - Tech Innovation
        ['Ryan Park', 'Digital Future', 'Software engineer and startup founder advocating for digital infrastructure investment.', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=200&h=200&fit=crop&crop=face', 6],
        ['Fatima Al-Rashid', 'Innovation Now', 'AI researcher pushing for ethical technology policies and digital literacy programs.', 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=200&h=200&fit=crop&crop=face', 6],
        ['Carlos Mendez', 'Tech for All', 'Cybersecurity expert focused on protecting citizens in the digital age.', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&h=200&fit=crop&crop=face', 6],
        // Election 7 - Environmental
        ['Dr. Lena Fischer', 'Green Earth', 'Climate scientist with 15 years of research on renewable energy and carbon reduction.', 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=200&h=200&fit=crop&crop=face', 7],
        ['Samuel Obi', 'Planet First', 'Environmental lawyer fighting for stronger pollution regulations and clean water access.', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=200&h=200&fit=crop&crop=face', 7],
        ['Yuki Tanaka', 'Sustainable Tomorrow', 'Renewable energy entrepreneur who has built solar projects across 10 countries.', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&h=200&fit=crop&crop=face', 7],
    ];
    $stmt = $conn->prepare("INSERT INTO candidates (name, party, bio, image, election_id) VALUES (?,?,?,?,?)");
    foreach ($candidates as $c) {
        $stmt->bind_param('ssssi', $c[0], $c[1], $c[2], $c[3], $c[4]);
        $stmt->execute();
    }
    $done[] = "✅ " . count($candidates) . " candidates inserted";
} else {
    $done[] = "ℹ️ Candidates already exist — skipped";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>VoteSecure Setup</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', sans-serif; background: #0f172a; color: #f1f5f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
    .card { background: #1e293b; border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 40px; max-width: 560px; width: 100%; box-shadow: 0 25px 50px rgba(0,0,0,0.5); }
    h1 { font-size: 1.8rem; margin-bottom: 8px; background: linear-gradient(135deg,#4facfe,#00f2fe); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    .subtitle { color: #94a3b8; margin-bottom: 28px; font-size: 0.9rem; }
    .item { padding: 10px 14px; border-radius: 10px; margin-bottom: 8px; font-size: 0.9rem; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); }
    .item.error { background: rgba(235,51,73,0.1); border-color: rgba(235,51,73,0.3); color: #f45c43; }
    .creds { background: rgba(79,172,254,0.08); border: 1px solid rgba(79,172,254,0.25); border-radius: 12px; padding: 16px 20px; margin: 20px 0; }
    .creds h3 { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; color: #4facfe; margin-bottom: 10px; }
    .cred-row { display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 6px; }
    .cred-val { font-family: monospace; color: #38ef7d; }
    .btn { display: inline-block; padding: 12px 28px; border-radius: 50px; font-weight: 700; text-decoration: none; font-size: 0.95rem; margin-top: 8px; margin-right: 8px; }
    .btn-primary { background: linear-gradient(135deg,#4facfe,#00f2fe); color: #0f172a; }
    .btn-outline { border: 2px solid rgba(255,255,255,0.2); color: #f1f5f9; }
    .warning { background: rgba(255,106,0,0.1); border: 1px solid rgba(255,106,0,0.3); border-radius: 10px; padding: 12px 16px; font-size: 0.82rem; color: #ff6a00; margin-top: 16px; }
  </style>
</head>
<body>
<div class="card">
  <h1>🗳️ VoteSecure Setup</h1>
  <p class="subtitle">Database initialization complete</p>

  <?php foreach ($done as $msg): ?>
  <div class="item"><?= $msg ?></div>
  <?php endforeach; ?>

  <?php foreach ($errors as $err): ?>
  <div class="item error"><?= $err ?></div>
  <?php endforeach; ?>

  <div class="creds">
    <h3>Admin Login Credentials</h3>
    <div class="cred-row"><span>Email</span><span class="cred-val">admin@votesystem.com</span></div>
    <div class="cred-row"><span>Password</span><span class="cred-val">Admin@123</span></div>
  </div>

  <a href="/online-voting-system/public/login.php" class="btn btn-primary">Go to Login →</a>
  <a href="/online-voting-system/public/index.php" class="btn btn-outline">Homepage</a>

  <div class="warning">
    ⚠️ Delete or restrict <code>setup.php</code> after setup is complete.
  </div>
</div>
</body>
</html>
