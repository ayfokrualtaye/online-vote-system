<?php
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/security.php';
require_once __DIR__ . '/../../models/User.php';

Auth::startSession();
Auth::requireAdmin('/online-voting-system/public/login.php');

$userModel = new User();
$message = '';
$csrf = Security::generateCSRF();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::validateCSRF($_POST['csrf_token'] ?? '')) {
        $message = ['type' => 'danger', 'text' => 'Invalid request token.'];
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'delete') {
            $userModel->delete((int)$_POST['id']);
            $message = ['type' => 'success', 'text' => 'User deleted.'];
        }
    }
}

$users = $userModel->getAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Users — VoteSecure</title>
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
      <h2>Manage <span class="gradient-text">Users</span></h2>
      <p>View and manage registered voters</p>
    </div>
    <span style="color:var(--text-muted);font-size:0.9rem;"><?= count($users) ?> total users</span>
  </div>

  <?php if ($message): ?>
  <div class="alert alert-<?= $message['type'] ?>"><?= $message['text'] ?></div>
  <?php endif; ?>

  <div class="table-wrapper reveal">
    <div class="table-header">
      <h3>All Users</h3>
    </div>
    <table>
      <thead>
        <tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Verified</th><th>Joined</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td><?= $u['id'] ?></td>
          <td><?= htmlspecialchars($u['name']) ?></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><span class="badge badge-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
          <td><?= $u['verified'] ? '✅' : '❌' ?></td>
          <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
          <td>
            <?php if ($u['role'] !== 'admin'): ?>
            <form method="POST" style="display:inline;"
                  onsubmit="return confirm('Delete this user?')">
              <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $u['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            </form>
            <?php else: ?>
            <span style="color:var(--text-muted);font-size:0.8rem;">Protected</span>
            <?php endif; ?>
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
