<?php
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/security.php';
require_once __DIR__ . '/../../models/Application.php';

Auth::startSession();
Auth::requireAdmin('/online-voting-system/public/login.php');

$appModel = new Application();
$message  = '';
$csrf     = Security::generateCSRF();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::validateCSRF($_POST['csrf_token'] ?? '')) {
        $message = ['type' => 'danger', 'text' => 'Invalid request token.'];
    } else {
        $action = $_POST['action'] ?? '';
        $appId  = (int)($_POST['app_id'] ?? 0);
        $note   = Security::sanitize($_POST['admin_note'] ?? '');

        if ($action === 'approve') {
            $appModel->approve($appId, $note);
            $message = ['type' => 'success', 'text' => 'Application approved. Candidate added to election.'];
        } elseif ($action === 'reject') {
            $appModel->reject($appId, $note);
            $message = ['type' => 'danger', 'text' => 'Application rejected.'];
        }
    }
}

$filter = $_GET['filter'] ?? 'pending';
$applications = $filter === 'all' ? $appModel->getAll() : $appModel->getPending();
$pendingCount = $appModel->countPending();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Candidate Applications — VoteSecure</title>
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
      <h2>Candidate <span class="gradient-text">Applications</span></h2>
      <p>Review and approve voter applications to run as candidates</p>
    </div>
    <div style="display:flex;gap:8px;">
      <a href="?filter=pending" class="btn <?= $filter === 'pending' ? 'btn-primary' : 'btn-outline' ?> btn-sm">
        Pending <?php if ($pendingCount): ?><span style="background:rgba(255,255,255,0.2);border-radius:50px;padding:1px 7px;font-size:0.75rem;"><?= $pendingCount ?></span><?php endif; ?>
      </a>
      <a href="?filter=all" class="btn <?= $filter === 'all' ? 'btn-primary' : 'btn-outline' ?> btn-sm">All</a>
    </div>
  </div>

  <?php if ($message): ?>
  <div class="alert alert-<?= $message['type'] ?> reveal"><?= $message['text'] ?></div>
  <?php endif; ?>

  <?php if (empty($applications)): ?>
  <div class="card text-center reveal" style="padding:60px;">
    <div style="font-size:3rem;margin-bottom:16px;">📭</div>
    <h3>No <?= $filter === 'pending' ? 'Pending' : '' ?> Applications</h3>
    <p>All caught up!</p>
  </div>
  <?php else: ?>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(360px,1fr));gap:20px;">
    <?php foreach ($applications as $app):
      $statusColors = ['pending' => 'badge-upcoming', 'approved' => 'badge-active', 'rejected' => 'badge-closed'];
    ?>
    <div class="card card-dark reveal" style="padding:24px;">
      <!-- Header -->
      <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:16px;">
        <div style="display:flex;align-items:center;gap:12px;">
          <img src="<?= $app['image'] ?: 'https://images.unsplash.com/photo-1633332755192-727a05c4013d?w=80&h=80&fit=crop&crop=face' ?>"
               style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid rgba(79,172,254,0.3);" alt="">
          <div>
            <div style="font-weight:700;"><?= htmlspecialchars($app['user_name']) ?></div>
            <div style="font-size:0.8rem;color:var(--text-muted);"><?= htmlspecialchars($app['user_email']) ?></div>
          </div>
        </div>
        <span class="badge <?= $statusColors[$app['status']] ?? 'badge-closed' ?>"><?= ucfirst($app['status']) ?></span>
      </div>

      <!-- Election & Party -->
      <div style="background:rgba(79,172,254,0.06);border:1px solid rgba(79,172,254,0.15);border-radius:10px;padding:12px;margin-bottom:14px;">
        <div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:2px;">Election</div>
        <div style="font-weight:600;font-size:0.9rem;"><?= htmlspecialchars($app['election_title']) ?></div>
        <div style="font-size:0.8rem;color:var(--primary-start);margin-top:4px;">🏛️ <?= htmlspecialchars($app['party']) ?></div>
      </div>

      <!-- Bio -->
      <div style="margin-bottom:12px;">
        <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);margin-bottom:4px;">Bio</div>
        <p style="font-size:0.85rem;line-height:1.6;"><?= htmlspecialchars(substr($app['bio'], 0, 120)) ?>...</p>
      </div>

      <!-- Manifesto preview -->
      <?php if ($app['manifesto']): ?>
      <div style="margin-bottom:14px;">
        <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);margin-bottom:4px;">Manifesto</div>
        <p style="font-size:0.82rem;line-height:1.6;color:var(--text-muted);"><?= htmlspecialchars(substr($app['manifesto'], 0, 150)) ?>...</p>
      </div>
      <?php endif; ?>

      <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:14px;">
        Applied: <?= date('M d, Y H:i', strtotime($app['applied_at'])) ?>
      </div>

      <!-- Actions (only for pending) -->
      <?php if ($app['status'] === 'pending'): ?>
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="app_id" value="<?= $app['id'] ?>">
        <div class="form-group">
          <input type="text" name="admin_note" class="form-control"
                 placeholder="Optional note to applicant..." style="font-size:0.85rem;">
        </div>
        <div style="display:flex;gap:8px;">
          <button type="submit" name="action" value="approve" class="btn btn-success w-100">✅ Approve</button>
          <button type="submit" name="action" value="reject"  class="btn btn-danger w-100">❌ Reject</button>
        </div>
      </form>
      <?php elseif ($app['admin_note']): ?>
      <div style="background:rgba(255,255,255,0.04);border-radius:8px;padding:10px;font-size:0.8rem;color:var(--text-muted);">
        <strong>Note:</strong> <?= htmlspecialchars($app['admin_note']) ?>
      </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<script src="/online-voting-system/assets/js/app.js"></script>
</body>
</html>
