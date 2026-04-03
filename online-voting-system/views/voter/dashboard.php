<?php
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../models/Election.php';
require_once __DIR__ . '/../../models/Vote.php';
require_once __DIR__ . '/../../models/Application.php';

Auth::startSession();
Auth::requireLogin('/online-voting-system/public/login.php');

$user      = Auth::currentUser();
$elections = (new Election())->getActive();
$voteModel = new Vote();
$myApps    = (new Application())->getByUser($user['id']);

foreach ($elections as &$e) {
    $e['has_voted']   = $voteModel->hasVoted($user['id'], $e['id']);
    $e['total_votes'] = $voteModel->getTotalVotes($e['id']);
}
unset($e);

$votedCount   = count(array_filter($elections, fn($e) => $e['has_voted']));
$pendingCount = count(array_filter($elections, fn($e) => !$e['has_voted']));
$appPending   = count(array_filter($myApps, fn($a) => $a['status'] === 'pending'));
$appApproved  = count(array_filter($myApps, fn($a) => $a['status'] === 'approved'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Voter Dashboard — VoteSecure</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/online-voting-system/assets/css/style.css">
  <link rel="stylesheet" href="/online-voting-system/assets/css/animations.css">
  <link rel="stylesheet" href="/online-voting-system/assets/css/themes.css">
</head>
<body class="theme-dashboard">
<canvas id="particles-canvas"></canvas>

<?php include __DIR__ . '/partials/navbar.php'; ?>

<div style="padding-top:90px;" class="container section">

  <!-- Welcome -->
  <div class="dashboard-header reveal">
    <div>
      <h2>Welcome back, <span class="gradient-text"><?= htmlspecialchars($user['name']) ?></span> 👋</h2>
      <p>Here's your voting overview</p>
    </div>
    <a href="/online-voting-system/views/voter/vote.php" class="btn btn-primary">🗳️ Vote Now</a>
  </div>

  <!-- Stats -->
  <div class="stats-grid reveal">
    <div class="stat-card blue">
      <div class="stat-icon blue">🗳️</div>
      <div class="stat-info">
        <h3><?= count($elections) ?></h3>
        <p>Active Elections</p>
      </div>
    </div>
    <div class="stat-card green">
      <div class="stat-icon green">✅</div>
      <div class="stat-info">
        <h3><?= $votedCount ?></h3>
        <p>Votes Cast</p>
      </div>
    </div>
    <div class="stat-card purple">
      <div class="stat-icon purple">⏳</div>
      <div class="stat-info">
        <h3><?= $pendingCount ?></h3>
        <p>Pending Votes</p>
      </div>
    </div>
    <div class="stat-card orange">
      <div class="stat-icon orange">📨</div>
      <div class="stat-info">
        <h3><?= count($myApps) ?></h3>
        <p>My Applications</p>
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="reveal mb-4">
    <h3 style="margin-bottom:16px;">Quick Actions</h3>
    <div style="display:flex;gap:12px;flex-wrap:wrap;">
      <a href="/online-voting-system/public/elections.php" class="btn btn-primary">🗳️ Browse Elections</a>
      <a href="/online-voting-system/views/voter/apply.php" class="btn btn-secondary">📝 Run for Office</a>
      <a href="/online-voting-system/views/voter/history.php" class="btn btn-outline">📋 My History</a>
      <a href="/online-voting-system/public/results.php" class="btn btn-outline">📊 View Results</a>
    </div>
  </div>

  <!-- Candidate Application Status -->
  <?php if (!empty($myApps)): ?>
  <div class="table-wrapper reveal mb-4">
    <div class="table-header">
      <h3>My Candidate Applications</h3>
      <a href="/online-voting-system/views/voter/apply.php" class="btn btn-outline btn-sm">+ New Application</a>
    </div>
    <table>
      <thead><tr><th>Election</th><th>Party</th><th>Status</th><th>Applied</th></tr></thead>
      <tbody>
        <?php foreach ($myApps as $app):
          $badge = ['pending'=>'badge-upcoming','approved'=>'badge-active','rejected'=>'badge-closed'][$app['status']] ?? 'badge-closed';
        ?>
        <tr>
          <td><?= htmlspecialchars($app['election_title']) ?></td>
          <td style="color:var(--primary-start);"><?= htmlspecialchars($app['party']) ?></td>
          <td><span class="badge <?= $badge ?>"><?= ucfirst($app['status']) ?></span></td>
          <td style="font-size:0.8rem;color:var(--text-muted);"><?= date('M d, Y', strtotime($app['applied_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <!-- Active Elections -->
  <div class="table-wrapper reveal mt-4">
    <div class="table-header">
      <h3>Active Elections</h3>
      <a href="vote.php" class="btn btn-primary btn-sm">Vote Now</a>
    </div>
    <?php if (empty($elections)): ?>
    <div class="text-center" style="padding:60px 20px;">
      <div style="font-size:3rem;margin-bottom:16px;">🗳️</div>
      <h3>No Active Elections</h3>
      <p>Check back later for upcoming elections.</p>
    </div>
    <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Election</th>
          <th>End Date</th>
          <th>Total Votes</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($elections as $e): ?>
        <tr>
          <td>
            <strong><?= htmlspecialchars($e['title']) ?></strong>
            <br><small style="color:var(--text-muted);"><?= htmlspecialchars(substr($e['description'], 0, 60)) ?>...</small>
          </td>
          <td><?= date('M d, Y', strtotime($e['end_date'])) ?></td>
          <td><?= number_format($e['total_votes']) ?></td>
          <td>
            <?php if ($e['has_voted']): ?>
            <span class="badge badge-active">✅ Voted</span>
            <?php else: ?>
            <span class="badge badge-upcoming">⏳ Pending</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($e['has_voted']): ?>
            <a href="/online-voting-system/public/results.php?election_id=<?= $e['id'] ?>" class="btn btn-outline btn-sm">View Results</a>
            <?php else: ?>
            <a href="/online-voting-system/views/voter/vote.php?election_id=<?= $e['id'] ?>" class="btn btn-primary btn-sm">Vote Now</a>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<script src="/online-voting-system/assets/js/app.js"></script>
</body>
</html>
