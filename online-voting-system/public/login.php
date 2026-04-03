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

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::validateCSRF($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please refresh and try again.';
    } elseif (!Security::checkRateLimit('login', 5, 300)) {
        $error = 'Too many attempts. Please wait 5 minutes.';
    } else {
        $email    = Security::sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $error = 'Email and password are required.';
        } else {
            $result = Auth::login($email, $password);
            if ($result['success']) {
                $redirect = $result['role'] === 'admin'
                    ? '/online-voting-system/views/admin/dashboard.php'
                    : '/online-voting-system/views/voter/dashboard.php';
                header("Location: $redirect");
                exit;
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
  <title>Login — VoteSecure</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/online-voting-system/assets/css/style.css">
  <link rel="stylesheet" href="/online-voting-system/assets/css/animations.css">
  <link rel="stylesheet" href="/online-voting-system/assets/css/themes.css">
</head>
<body class="theme-auth">

<canvas id="particles-canvas"></canvas>
<div class="auth-shape auth-shape-1"></div>
<div class="auth-shape auth-shape-2"></div>

<div class="auth-card">
  <div class="auth-logo">
    <div class="logo-circle">🗳️</div>
    <h2>Welcome Back</h2>
    <p>Sign in to your VoteSecure account</p>
  </div>

  <?php if ($error): ?>
  <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

    <div class="form-group">
      <label class="form-label" for="email">Email Address</label>
      <div class="input-icon">
        <span class="icon">📧</span>
        <input type="email" id="email" name="email" class="form-control"
               placeholder="you@example.com" required autocomplete="email"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
    </div>

    <div class="form-group">
      <label class="form-label" for="password">Password</label>
      <div class="input-icon" style="position:relative;">
        <span class="icon">🔒</span>
        <input type="password" id="password" name="password" class="form-control"
               placeholder="Enter your password" required autocomplete="current-password"
               style="padding-right:50px;">
        <button type="button" class="toggle-password" data-target="#password"
                style="position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:1rem;">
          👁️
        </button>
      </div>
    </div>

    <button type="submit" class="btn btn-primary w-100 mt-2">
      Sign In
    </button>
  </form>

  <p class="text-center mt-3" style="font-size:0.9rem;">
    Don't have an account?
    <a href="/online-voting-system/public/register.php" style="color:var(--primary-start);font-weight:600;">Create one</a>
  </p>

  <p class="text-center mt-2" style="font-size:0.8rem;color:var(--text-muted);">
    Demo: admin@votesystem.com / Admin@123
  </p>
</div>

<script src="/online-voting-system/assets/js/app.js"></script>
<script>
document.querySelectorAll('.toggle-password').forEach(btn => {
  btn.addEventListener('click', () => {
    const input = document.querySelector(btn.dataset.target);
    const isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    btn.textContent = isText ? '👁️' : '🙈';
  });
});
</script>
</body>
</html>
