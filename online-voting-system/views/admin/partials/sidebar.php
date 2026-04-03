<?php
$user = Auth::currentUser();
$base = '/online-voting-system/views/admin';
$cur  = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="logo-icon">🗳️</div>
    <span>VoteSecure</span>
  </div>

  <div class="sidebar-section">Main</div>
  <ul class="sidebar-nav">
    <li>
      <a href="<?= $base ?>/dashboard.php" <?= $cur === 'dashboard.php' ? 'class="active"' : '' ?>>
        <span class="nav-icon">📊</span> Dashboard
      </a>
    </li>
    <li>
      <a href="<?= $base ?>/elections.php" <?= $cur === 'elections.php' ? 'class="active"' : '' ?>>
        <span class="nav-icon">🗳️</span> Elections
      </a>
    </li>
    <li>
      <a href="<?= $base ?>/candidates.php" <?= $cur === 'candidates.php' ? 'class="active"' : '' ?>>
        <span class="nav-icon">👤</span> Candidates
      </a>
    </li>
    <li>
      <a href="<?= $base ?>/users.php" <?= $cur === 'users.php' ? 'class="active"' : '' ?>>
        <span class="nav-icon">👥</span> Users
      </a>
    </li>
    <li>
      <a href="<?= $base ?>/results.php" <?= $cur === 'results.php' ? 'class="active"' : '' ?>>
        <span class="nav-icon">📈</span> Results
      </a>
    </li>
    <li>
      <a href="<?= $base ?>/applications.php" <?= $cur === 'applications.php' ? 'class="active"' : '' ?>>
        <span class="nav-icon">📨</span> Applications
        <?php
        require_once __DIR__ . '/../../../models/Application.php';
        $pending = (new Application())->countPending();
        if ($pending > 0): ?>
        <span style="background:linear-gradient(135deg,#ff6a00,#ee0979);color:#fff;border-radius:50px;padding:1px 7px;font-size:0.7rem;margin-left:auto;"><?= $pending ?></span>
        <?php endif; ?>
      </a>
    </li>
    <li>
      <a href="<?= $base ?>/logs.php" <?= $cur === 'logs.php' ? 'class="active"' : '' ?>>
        <span class="nav-icon">📋</span> Activity Logs
      </a>
    </li>
  </ul>

  <div class="sidebar-section">Account</div>
  <ul class="sidebar-nav">
    <li>
      <a href="#" style="cursor:default;">
        <span class="nav-icon">👤</span>
        <?= htmlspecialchars($user['name'] ?? 'Admin') ?>
        <span class="badge badge-admin" style="margin-left:auto;font-size:0.65rem;">Admin</span>
      </a>
    </li>
    <li>
      <a href="/online-voting-system/public/logout.php">
        <span class="nav-icon">🚪</span> Logout
      </a>
    </li>
  </ul>
</aside>
