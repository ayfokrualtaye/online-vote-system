<?php
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../models/Election.php';

Auth::startSession();
Auth::requireLogin('/online-voting-system/public/login.php');

$user = Auth::currentUser();
$db   = Database::getInstance();

// Voting history — elections the user has voted in
$votingHistory = $db->fetchAll(
    "SELECT vr.voted_at, e.id as election_id, e.title, e.status, e.end_date
     FROM voter_registry vr
     JOIN elections e ON e.id = vr.election_id
     WHERE vr.user_id = ?
     ORDER BY vr.voted_at DESC",
    'i', [$user['id']]
);

// Activity log for this user
$activities = $db->fetchAll(
    "SELECT action, details, ip_address, created_at
     FROM activity_logs
     WHERE user_id = ?
     ORDER BY created_at DESC
     LIMIT 50",
    'i', [$user['id']]
);

// Stats
$totalVotes    = count($votingHistory);
$activeElections = (new Election())->getActive();
$totalActive   = count($activeElections);
$pending       = 0;
foreach ($activeElections as $e) {
    $voted = $db->fetchOne(
        "SELECT id FROM voter_registry WHERE user_id = ? AND election_id = ?",
        'ii', [$user['id'], $e['id']]
    );
    if (!$voted) $pending++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Voting History — VoteSecure</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/online-voting-system/assets/css/style.css">
  <link rel="stylesheet" href="/online-voting-system/assets/css/animations.css">
  <link rel="stylesheet" href="/online-voting-system/assets/css/themes.css">
</head>
<body class="theme-dashboard">
<canvas id="particles-canvas"></canvas>

<?php include __DIR__ . '/partials/navbar.php'; ?>

<div style="padding-top:90px;" class="container section">

  <div class="dashboard-header reveal">
    <div>
      <h2>Voting <span class="gradient-text">History</span></h2>
      <p>Your complete voting record and activity</p>
    </div>
  </div>

  <!-- Stats -->
  <div class="stats-grid reveal">
    <div class="stat-card blue">
      <div class="stat-icon blue">🗳️</div>
      <div class="stat-info">
        <h3><?= $totalVotes ?></h3>
        <p>Total Votes Cast</p>
      </div>
    </div>
    <div class="stat-card green">
      <div class="stat-icon green">✅</div>
      <div class="stat-info">
        <h3><?= $totalActive ?></h3>
        <p>Active Elections</p>
      </div>
    </div>
    <div class="stat-card orange">
      <div class="stat-icon orange">⏳</div>
      <div class="stat-info">
        <h3><?= $pending ?></h3>
        <p>Pending Your Vote</p>
      </div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">

    <!-- Voting History -->
    <div class="table-wrapper reveal">
      <div class="table-header">
        <h3>Elections Voted In</h3>
        <span style="font-size:0.85rem;color:var(--text-muted);"><?= $totalVotes ?> total</span>
      </div>
      <?php if (empty($votingHistory)): ?>
      <div class="text-center" style="padding:40px;">
        <div style="font-size:3rem;margin-bottom:12px;">🗳️</div>
        <p>You haven't voted in any elections yet.</p>
        <a href="/online-voting-system/public/elections.php" class="btn btn-primary btn-sm mt-2">Browse Elections</a>
      </div>
      <?php else: ?>
      <table>
        <thead>
          <tr><th>Election</th><th>Status</th><th>Voted On</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach ($votingHistory as $v): ?>
          <tr>
            <td>
              <strong style="font-size:0.9rem;"><?= htmlspecialchars($v['title']) ?></strong>
            </td>
            <td><span class="badge badge-<?= $v['status'] ?>"><?= ucfirst($v['status']) ?></span></td>
            <td style="font-size:0.8rem;color:var(--text-muted);"><?= date('M d, Y', strtotime($v['voted_at'])) ?></td>
            <td>
              <a href="/online-voting-system/public/results.php?election_id=<?= $v['election_id'] ?>"
                 class="btn btn-outline btn-sm">Results</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>

    <!-- Activity Log -->
    <div class="table-wrapper reveal">
      <div class="table-header">
        <h3>Activity Log</h3>
        <span style="font-size:0.85rem;color:var(--text-muted);">Last 50 actions</span>
      </div>
      <?php if (empty($activities)): ?>
      <div class="text-center" style="padding:40px;">
        <div style="font-size:3rem;margin-bottom:12px;">📋</div>
        <p>No activity recorded yet.</p>
      </div>
      <?php else: ?>
      <table>
        <thead>
          <tr><th>Action</th><th>Time</th></tr>
        </thead>
        <tbody>
          <?php foreach ($activities as $a):
            $icons = [
              'login'      => '🔐',
              'logout'     => '🚪',
              'vote_cast'  => '🗳️',
              'register'   => '📝',
            ];
            $icon = '📌';
            foreach ($icons as $k => $v) {
              if (str_contains($a['action'], $k)) { $icon = $v; break; }
            }
          ?>
          <tr>
            <td>
              <span style="margin-right:6px;"><?= $icon ?></span>
              <span style="font-size:0.85rem;"><?= htmlspecialchars($a['action']) ?></span>
              <?php if ($a['details']): ?>
              <br><small style="color:var(--text-muted);"><?= htmlspecialchars($a['details']) ?></small>
              <?php endif; ?>
            </td>
            <td style="font-size:0.75rem;color:var(--text-muted);white-space:nowrap;">
              <?= date('M d, H:i', strtotime($a['created_at'])) ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>

  <!-- Pending Elections -->
  <?php if ($pending > 0): ?>
  <div class="mt-4 reveal">
    <h3 style="margin-bottom:16px;">⏳ Elections Awaiting Your Vote</h3>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;">
      <?php foreach ($activeElections as $e):
        $voted = $db->fetchOne(
            "SELECT id FROM voter_registry WHERE user_id = ? AND election_id = ?",
            'ii', [$user['id'], $e['id']]
        );
        if ($voted) continue;
      ?>
      <div class="card card-dark" style="padding:20px;display:flex;align-items:center;gap:16px;">
        <div style="width:48px;height:48px;border-radius:12px;background:rgba(79,172,254,0.15);display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0;">🗳️</div>
        <div style="flex:1;min-width:0;">
          <div style="font-weight:700;font-size:0.9rem;margin-bottom:2px;"><?= htmlspecialchars($e['title']) ?></div>
          <div style="font-size:0.75rem;color:var(--text-muted);">Ends <?= date('M d, Y', strtotime($e['end_date'])) ?></div>
        </div>
        <a href="/online-voting-system/views/voter/vote.php?election_id=<?= $e['id'] ?>"
           class="btn btn-primary btn-sm">Vote</a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

</div>

<script src="/online-voting-system/assets/js/app.js"></script>
</body>
</html>
