<?php
// Reset admin password — visit once then delete
$conn = new mysqli('localhost', 'root', '', 'voting_system');

if ($conn->connect_error) {
    die('MySQL not running: ' . $conn->connect_error);
}

$newPassword = 'Admin@123';
$hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

// Update existing admin or insert fresh
$existing = $conn->query("SELECT id FROM users WHERE email = 'admin@votesystem.com'")->fetch_assoc();

if ($existing) {
    $stmt = $conn->prepare("UPDATE users SET password = ?, verified = 1 WHERE email = 'admin@votesystem.com'");
    $stmt->bind_param('s', $hash);
    $stmt->execute();
    echo "<h2 style='font-family:sans-serif;color:green;padding:20px;'>✅ Admin password reset successfully!</h2>";
} else {
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, verified) VALUES ('Administrator', 'admin@votesystem.com', ?, 'admin', 1)");
    $stmt->bind_param('s', $hash);
    $stmt->execute();
    echo "<h2 style='font-family:sans-serif;color:green;padding:20px;'>✅ Admin user created!</h2>";
}

echo "<p style='font-family:sans-serif;padding:0 20px;'>
    <strong>Email:</strong> admin@votesystem.com<br>
    <strong>Password:</strong> Admin@123<br><br>
    <a href='/online-voting-system/public/login.php' 
       style='background:#4facfe;color:#000;padding:10px 24px;border-radius:50px;text-decoration:none;font-weight:bold;'>
       → Go to Login
    </a>
</p>";

// Verify it works
$check = $conn->query("SELECT password FROM users WHERE email = 'admin@votesystem.com'")->fetch_assoc();
$valid = password_verify('Admin@123', $check['password']);
echo "<p style='font-family:sans-serif;padding:10px 20px;color:" . ($valid ? 'green' : 'red') . ";'>
    Password verify check: " . ($valid ? '✅ PASS' : '❌ FAIL') . "
</p>";
?>
