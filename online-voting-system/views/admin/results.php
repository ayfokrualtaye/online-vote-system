<?php
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../models/Election.php';
require_once __DIR__ . '/../../models/Vote.php';

Auth::startSession();
Auth::requireAdmin('/online-voting-system/public/login.php');

$electionModel = new Election();
$voteModel     = new Vote();
$elections     = $electionModel->getAll();
$electionId    = (int)($_GET['election_id'] ?? ($elections[0]['id'] ?? 0));
$election      = $electionId ? $electionModel->getById($electionId) : null;
$results       = $electionId ? $voteModel->getResults($electionId) : [];
$total         = $electionId ? $voteModel->getTotalVotes($electionId) : 0;
$labels        = array_column($results, 'name');
$data          = array_column($results, 'vote_count');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Results — VoteSecure Admin</title>
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
      <h2>Election <span class="gradient-text">Results</span></h2>
      <p>Detailed vote analytics and reports</p>
    </div>
    <select class="form-control" style="max-width:280px;"
            onchange="window.location.href='results.php?election_id='+this.value">
      <?php foreach ($elections as $e): ?>
      <option value="<?= $e['id'] ?>" <?= $e['id'] == $electionId ? 'selected' : '' ?>>
        <?= htmlspecialchars($e['title']) ?>
      </option>
      <?php endforeach; ?>
    </select>
  </div>

  <?php if ($election): ?>
  <!-- Summary -->
  <div class="stats-grid reveal">
    <div class="stat-card blue">
      <div class="stat-icon blue">🗳️</div>
      <div class="stat-info">
        <h3><?= number_format($total) ?></h3>
        <p>Total Votes</p>
      </div>
    </div>
    <div class="stat-card purple">
      <div class="stat-icon purple">👤</div>
      <div class="stat-info">
        <h3><?= count($results) ?></h3>
        <p>Candidates</p>
      </div>
    </div>
    <div class="stat-card green">
      <div class="stat-icon green">🏆</div>
      <div class="stat-info">
        <h3><?= htmlspecialchars($results[0]['name'] ?? 'N/A') ?></h3>
        <p>Current Leader</p>
      </div>
    </div>
    <div class="stat-card orange">
      <div class="stat-icon orange">📊</div>
      <div class="stat-info">
        <h3><?= $results[0]['percentage'] ?? 0 ?>%</h3>
        <p>Leader's Share</p>
      </div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;" class="reveal">
    <!-- Progress Bars -->
    <div class="chart-container">
      <div class="chart-title">Vote Breakdown</div>
      <?php foreach ($results as $i => $r): ?>
      <div class="result-bar-item">
        <div class="result-bar-header">
          <span class="result-bar-name"><?= htmlspecialchars($r['name']) ?></span>
          <span class="result-bar-count"><?= number_format($r['vote_count']) ?> · <?= $r['percentage'] ?? 0 ?>%</span>
        </div>
        <div class="result-progress">
          <div class="result-progress-fill <?= $i === 1 ? 'second' : ($i === 2 ? 'third' : '') ?>"
               data-width="<?= $r['percentage'] ?? 0 ?>%"></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Doughnut -->
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
  <?php endif; ?>
</div>

<script src="/online-voting-system/assets/js/app.js"></script>
<script src="/online-voting-system/assets/js/vote.js"></script>
<script>
  const labels = <?= json_encode($labels) ?>;
  const data   = <?= json_encode(array_map('intval', $data)) ?>;
  initResultsChart('doughnut-chart', labels, data);
  initBarChart('bar-chart', labels, data);
</script>
</body>
</html>
