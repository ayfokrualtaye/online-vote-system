<?php
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Election.php';
require_once __DIR__ . '/../../models/Vote.php';
require_once __DIR__ . '/../../models/Candidate.php';

Auth::startSession();
Auth::requireAdmin('/online-voting-system/public/login.php');

$userModel     = new User();
$electionModel = new Election();
$voteModel     = new Vote();
$candidateModel= new Candidate();

$totalVoters     = $userModel->getTotalVoters();
$totalElections  = $electionModel->getTotal();
$totalVotes      = $voteModel->getTotalVotesAll();
$totalCandidates = $candidateModel->getTotal();
$activeElections = $electionModel->getActive();
$recentLogs      = Database::getInstance()->fetchAll(
    "SELECT l.*, u.name FROM activity_logs l LEFT JOIN users u ON u.id = l.user_id ORDER BY l.created_at DESC LIMIT 10"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard — VoteSecure</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/online-voting-system/assets/css/style.css">
  <link rel="stylesheet" href="/online-voting-system/assets/css/animations.css">
  <link rel="stylesheet" href="/online-voting-system/assets/css/themes.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="theme-dashboard">

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="dashboard-main">
  <div class="dashboard-header reveal">
    <div>
      <h2>Admin <span class="gradient-text">Dashboard</span></h2>
      <p>System overview and management</p>
    </div>
    <div style="display:flex;gap:12px;">
      <a href="elections.php" class="btn btn-primary btn-sm">+ New Election</a>
      <a href="candidates.php" class="btn btn-secondary btn-sm">+ Add Candidate</a>
    </div>
  </div>

  <!-- Stats -->
  <div class="stats-grid reveal">
    <div class="stat-card blue">
      <div class="stat-icon blue">👥</div>
      <div class="stat-info">
        <h3 data-count="<?= $totalVoters ?>"><?= $totalVoters ?></h3>
        <p>Registered Voters</p>
      </div>
    </div>
    <div class="stat-card purple">
      <div class="stat-icon purple">🗳️</div>
      <div class="stat-info">
        <h3 data-count="<?= $totalElections ?>"><?= $totalElections ?></h3>
        <p>Total Elections</p>
      </div>
    </div>
    <div class="stat-card orange">
      <div class="stat-icon orange">📊</div>
      <div class="stat-info">
        <h3 data-count="<?= $totalVotes ?>"><?= $totalVotes ?></h3>
        <p>Total Votes Cast</p>
      </div>
    </div>
    <div class="stat-card green">
      <div class="stat-icon green">👤</div>
      <div class="stat-info">
        <h3 data-count="<?= $totalCandidates ?>"><?= $totalCandidates ?></h3>
        <p>Candidates</p>
      </div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;" class="reveal">
    <!-- Active Elections -->
    <div class="table-wrapper">
      <div class="table-header">
        <h3>Active Elections</h3>
        <a href="elections.php" class="btn btn-outline btn-sm">Manage</a>
      </div>
      <?php if (empty($activeElections)): ?>
      <p class="text-center" style="padding:30px;">No active elections.</p>
      <?php else: ?>
      <table>
        <thead><tr><th>Title</th><th>Votes</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($activeElections as $e): ?>
          <tr>
            <td><?= htmlspecialchars($e['title']) ?></td>
            <td><?= $voteModel->getTotalVotes($e['id']) ?></td>
            <td><span class="badge badge-active">Active</span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>

    <!-- Recent Activity -->
    <div class="table-wrapper">
      <div class="table-header">
        <h3>Recent Activity</h3>
        <a href="logs.php" class="btn btn-outline btn-sm">View All</a>
      </div>
      <table>
        <thead><tr><th>User</th><th>Action</th><th>Time</th></tr></thead>
        <tbody>
          <?php foreach ($recentLogs as $log): ?>
          <tr>
            <td><?= htmlspecialchars($log['name'] ?? 'System') ?></td>
            <td><?= htmlspecialchars($log['action']) ?></td>
            <td style="font-size:0.8rem;color:var(--text-muted);">
              <?= date('M d, H:i', strtotime($log['created_at'])) ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="/online-voting-system/assets/js/app.js"></script>
</body>
</html>
