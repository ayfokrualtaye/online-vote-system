<?php
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/database.php';

Auth::startSession();
Auth::requireAdmin('/online-voting-system/public/login.php');

$db   = Database::getInstance();
$logs = $db->fetchAll(
    "SELECT l.*, u.name, u.email FROM activity_logs l
     LEFT JOIN users u ON u.id = l.user_id
     ORDER BY l.created_at DESC LIMIT 200"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Activity Logs — VoteSecure</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/online-voting-system/assets/css/style.css">
  <link rel="stylesheet" href="/online-voting-system/assets/css/animations.css">
  <link rel="stylesheet" href="/online-voting-system/assets/css/themes.css">
</head>
<body class="theme-dashboard">

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="dashboard-main">
  <div class="dashboard-header reveal">
    <div>
      <h2>Activity <span class="gradient-text">Logs</span></h2>
      <p>System audit trail — last 200 entries</p>
    </div>
  </div>

  <div class="table-wrapper reveal">
    <div class="table-header">
      <h3>All Activity (<?= count($logs) ?>)</h3>
    </div>
    <table>
      <thead>
        <tr><th>Time</th><th>User</th><th>Action</th><th>IP Address</th></tr>
      </thead>
      <tbody>
        <?php foreach ($logs as $log): ?>
        <tr>
          <td style="font-size:0.8rem;color:var(--text-muted);">
            <?= date('M d, Y H:i:s', strtotime($log['created_at'])) ?>
          </td>
          <td>
            <?= htmlspecialchars($log['name'] ?? 'System') ?>
            <?php if ($log['email']): ?>
            <br><small style="color:var(--text-muted);"><?= htmlspecialchars($log['email']) ?></small>
            <?php endif; ?>
          </td>
          <td>
            <span style="
              padding:3px 10px;border-radius:50px;font-size:0.8rem;
              background:<?= str_contains($log['action'], 'vote') ? 'rgba(79,172,254,0.15)' : 'rgba(255,255,255,0.05)' ?>;
              color:<?= str_contains($log['action'], 'vote') ? '#4facfe' : 'var(--text-muted)' ?>;
            ">
              <?= htmlspecialchars($log['action']) ?>
            </span>
          </td>
          <td style="font-size:0.85rem;font-family:monospace;color:var(--text-muted);">
            <?= htmlspecialchars($log['ip_address'] ?? '-') ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="/online-voting-system/assets/js/app.js"></script>
</body>
</html>
