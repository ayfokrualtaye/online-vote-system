<?php
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../models/Election.php';
require_once __DIR__ . '/../models/Vote.php';
require_once __DIR__ . '/../models/Candidate.php';

Auth::startSession();
Auth::requireLogin('/online-voting-system/public/login.php');

$electionId = (int)($_GET['election_id'] ?? 0);
$elections  = (new Election())->getActive();

if (!$electionId && !empty($elections)) {
    $electionId = $elections[0]['id'];
}

$voteModel = new Vote();
$results   = $electionId ? $voteModel->getResults($electionId) : [];
$total     = $electionId ? $voteModel->getTotalVotes($electionId) : 0;
$election  = $electionId ? (new Election())->getById($electionId) : null;
$winner    = !empty($results) ? $results[0] : null;
$justVoted = isset($_GET['voted']);

$labels = array_column($results, 'name');
$data   = array_column($results, 'vote_count');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Results — VoteSecure</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="../assets/css/animations.css">
  <link rel="stylesheet" href="../assets/css/themes.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="theme-results">
<canvas id="particles-canvas"></canvas>

<?php include __DIR__ . '/../views/voter/partials/navbar.php'; ?>

<div style="padding-top:90px;" class="container section">

  <?php if ($justVoted): ?>
  <div class="alert alert-success mb-3 animate-fade-in">
    ✅ Your vote has been cast successfully and recorded anonymously!
  </div>
  <?php endif; ?>

  <!-- Header -->
  <div class="results-header reveal">
    <p style="color:var(--success);font-size:0.85rem;font-weight:700;text-transform:uppercase;letter-spacing:2px;margin-bottom:8px;">📊 Live Results</p>
    <h2><?= htmlspecialchars($election['title'] ?? 'Election Results') ?></h2>
    <p>Total votes cast: <strong style="color:var(--text-primary);"><?= number_format($total) ?></strong></p>
  </div>

  <!-- Election Selector -->
  <?php if (count($elections) > 1): ?>
  <div class="mb-3">
    <select class="form-control" style="max-width:300px;"
            onchange="window.location.href='results.php?election_id='+this.value">
      <?php foreach ($elections as $e): ?>
      <option value="<?= $e['id'] ?>" <?= $e['id'] == $electionId ? 'selected' : '' ?>>
        <?= htmlspecialchars($e['title']) ?>
      </option>
      <?php endforeach; ?>
    </select>
  </div>
  <?php endif; ?>

  <?php if ($winner && $total > 0): ?>
  <!-- Winner Card -->
  <div class="winner-card mb-4 reveal">
    <div class="winner-badge">🏆 Current Leader</div>
    <img src="<?= strpos($winner['image'], 'http') === 0 ? $winner['image'] : '../assets/images/candidates/' . htmlspecialchars($winner['image']) ?>"
         onerror="this.src='https://images.unsplash.com/photo-1633332755192-727a05c4013d?w=200&h=200&fit=crop&crop=face'"
         style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid rgba(79,172,254,0.5);margin-bottom:12px;">
    <h3 class="gradient-text"><?= htmlspecialchars($winner['name']) ?></h3>
    <p><?= htmlspecialchars($winner['party']) ?></p>
    <div style="font-size:2rem;font-weight:800;color:var(--primary-start);margin-top:8px;">
      <?= $winner['percentage'] ?? 0 ?>%
    </div>
    <p style="font-size:0.85rem;"><?= number_format($winner['vote_count']) ?> votes</p>
  </div>
  <?php endif; ?>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;" class="reveal">
    <!-- Progress Bars -->
    <div class="chart-container">
      <div class="chart-title">Vote Distribution</div>
      <?php foreach ($results as $i => $r): ?>
      <div class="result-bar-item">
        <div class="result-bar-header">
          <span class="result-bar-name"><?= htmlspecialchars($r['name']) ?></span>
          <span class="result-bar-count"><?= number_format($r['vote_count']) ?> votes · <?= $r['percentage'] ?? 0 ?>%</span>
        </div>
        <div class="result-progress">
          <div class="result-progress-fill <?= $i === 1 ? 'second' : ($i === 2 ? 'third' : '') ?>"
               data-width="<?= $r['percentage'] ?? 0 ?>%"></div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if (empty($results)): ?>
      <p class="text-center" style="padding:40px 0;">No votes cast yet.</p>
      <?php endif; ?>
    </div>

    <!-- Doughnut Chart -->
    <div class="chart-container">
      <div class="chart-title">Vote Share</div>
      <div style="height:280px;position:relative;">
        <canvas id="doughnut-chart"></canvas>
      </div>
    </div>
  </div>

  <!-- Bar Chart -->
  <div class="chart-container mt-3 reveal">
    <div class="chart-title">Votes by Candidate</div>
    <div style="height:300px;position:relative;">
      <canvas id="bar-chart"></canvas>
    </div>
  </div>

  <div class="text-center mt-4">
    <a href="../views/voter/dashboard.php" class="btn btn-outline">← Back to Dashboard</a>
  </div>
</div>

<script src="../assets/js/app.js"></script>
<script src="../assets/js/vote.js"></script>
<script>
  const labels = <?= json_encode($labels) ?>;
  const data   = <?= json_encode(array_map('intval', $data)) ?>;
  initResultsChart('doughnut-chart', labels, data);
  initBarChart('bar-chart', labels, data);
</script>
</body>
</html>
