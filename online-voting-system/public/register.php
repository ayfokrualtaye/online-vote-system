<?php
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/security.php';

Auth::startSession();

if (Auth::isLoggedIn()) {
    header('Location: ' . (Auth::isAdmin()
        ? '/online-voting-system/views/admin/dashboard.php'
        : '/online-voting-system/views/voter/dashboard.php'));
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!Security::validateCSRF($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please refresh and try again.';
    } else {
        $name     = Security::sanitize($_POST['name'] ?? '');
        $email    = Security::sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (!$name || !$email || !$password || !$confirm) {
            $error = 'All fields are required.';
        } elseif (!Security::validateEmail($email)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $result = Auth::register($name, $email, $password);
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
        }
    }
}

$csrf = Security::generateCSRF();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register — VoteSecure</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/online-voting-system/assets/css/style.css">
  <link rel="stylesheet" href="/online-voting-system/assets/css/animations.css">
  <link rel="stylesheet" href="/online-voting-system/assets/css/themes.css">
</head>
<body class="theme-auth">

<canvas id="particles-canvas"></canvas>
<div class="auth-shape auth-shape-1"></div>
<div class="auth-shape auth-shape-2"></div>

<div class="auth-card" style="max-width:480px;">
  <div class="auth-logo">
    <div class="logo-circle">🗳️</div>
    <h2>Create Account</h2>
    <p>Join VoteSecure and make your voice heard</p>
  </div>

  <?php if ($error): ?>
  <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if ($success): ?>
  <div class="alert alert-success">
    ✅ <?= htmlspecialchars($success) ?>
    — <a href="/online-voting-system/public/login.php" style="color:inherit;font-weight:700;">Sign in now →</a>
  </div>
  <?php endif; ?>

  <form method="POST" action="" novalidate>
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

    <div class="form-group">
      <label class="form-label" for="name">Full Name</label>
      <div class="input-icon">
        <span class="icon">👤</span>
        <input type="text" id="name" name="name" class="form-control"
               placeholder="Your full name" required
               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
      </div>
    </div>

    <div class="form-group">
      <label class="form-label" for="email">Email Address</label>
      <div class="input-icon">
        <span class="icon">📧</span>
        <input type="email" id="email" name="email" class="form-control"
               placeholder="you@example.com" required
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
    </div>

    <div class="form-group">
      <label class="form-label" for="password">Password</label>
      <div class="input-icon" style="position:relative;">
        <span class="icon">🔒</span>
        <input type="password" id="password" name="password" class="form-control"
               placeholder="Min. 8 characters" required style="padding-right:50px;">
        <button type="button" class="toggle-password" data-target="#password"
                style="position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:1rem;">
          👁️
        </button>
      </div>
      <div style="margin-top:8px;">
        <div style="height:4px;background:rgba(255,255,255,0.08);border-radius:50px;overflow:hidden;">
          <div id="strength-bar" style="height:100%;width:0;border-radius:50px;transition:all 0.3s ease;"></div>
        </div>
        <span id="strength-label" style="font-size:0.75rem;font-weight:600;"></span>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label" for="confirm_password">Confirm Password</label>
      <div class="input-icon">
        <span class="icon">🔒</span>
        <input type="password" id="confirm_password" name="confirm_password" class="form-control"
               placeholder="Repeat your password" required>
      </div>
    </div>

    <button type="submit" class="btn btn-secondary w-100 mt-2">
      Create Account
    </button>
  </form>

  <p class="text-center mt-3" style="font-size:0.9rem;">
    Already have an account?
    <a href="/online-voting-system/public/login.php" style="color:var(--primary-start);font-weight:600;">Sign in</a>
  </p>
</div>

<script src="/online-voting-system/assets/js/app.js"></script>
<script>
// Password toggle
document.querySelectorAll('.toggle-password').forEach(btn => {
  btn.addEventListener('click', () => {
    const input = document.querySelector(btn.dataset.target);
    const isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    btn.textContent = isText ? '👁️' : '🙈';
  });
});

// Password strength
document.getElementById('password').addEventListener('input', function() {
  const bar = document.getElementById('strength-bar');
  const label = document.getElementById('strength-label');
  const pw = this.value;
  let score = 0;
  if (pw.length >= 8) score++;
  if (/[A-Z]/.test(pw)) score++;
  if (/[0-9]/.test(pw)) score++;
  if (/[^A-Za-z0-9]/.test(pw)) score++;
  const levels = [
    { label: 'Too weak', color: '#eb3349', width: '25%' },
    { label: 'Weak',     color: '#ff6a00', width: '50%' },
    { label: 'Good',     color: '#4facfe', width: '75%' },
    { label: 'Strong',   color: '#38ef7d', width: '100%' }
  ];
  const level = levels[Math.max(0, score - 1)] || levels[0];
  bar.style.width = pw.length ? level.width : '0';
  bar.style.background = level.color;
  label.textContent = pw.length ? level.label : '';
  label.style.color = level.color;
});
</script>
</body>
</html>
