<?php
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../models/Election.php';
require_once __DIR__ . '/../models/Vote.php';
require_once __DIR__ . '/../models/Candidate.php';

Auth::startSession();

$electionModel  = new Election();
$voteModel      = new Vote();
$candidateModel = new Candidate();
$allElections   = $electionModel->getAll();

// Enrich each election
foreach ($allElections as &$e) {
    $e['total_votes']      = $voteModel->getTotalVotes($e['id']);
    $e['total_candidates'] = count($candidateModel->getByElection($e['id']));
    $e['candidates']       = array_slice($candidateModel->getByElection($e['id']), 0, 3);
    if (Auth::isLoggedIn()) {
        $user = Auth::currentUser();
        $e['has_voted'] = $voteModel->hasVoted($user['id'], $e['id']);
    }
}
unset($e);

$electionImages = [
    1 => 'https://images.unsplash.com/photo-1494172961521-33799ddd43a5?w=800&h=400&fit=crop',
    2 => 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=800&h=400&fit=crop',
    3 => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=800&h=400&fit=crop',
    4 => 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=800&h=400&fit=crop',
    5 => 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?w=800&h=400&fit=crop',
    6 => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=800&h=400&fit=crop',
    7 => 'https://images.unsplash.com/photo-1497435334941-8c899ee9e8e9?w=800&h=400&fit=crop',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Elections — VoteSecure</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/online-voting-system/assets/css/style.css">
  <link rel="stylesheet" href="/online-voting-system/assets/css/animations.css">
  <link rel="stylesheet" href="/online-voting-system/assets/css/themes.css">
  <style>
    .election-card {
      background: var(--dark-card);
      border: 1px solid var(--dark-border);
      border-radius: var(--radius);
      overflow: hidden;
      transition: var(--transition);
    }
    .election-card:hover {
      transform: translateY(-6px);
      border-color: rgba(79,172,254,0.4);
      box-shadow: 0 24px 48px rgba(0,0,0,0.4);
    }
    .election-card-img {
      position: relative;
      height: 200px;
      overflow: hidden;
    }
    .election-card-img img {
      width: 100%; height: 100%;
      object-fit: cover;
      transition: transform 0.5s ease;
    }
    .election-card:hover .election-card-img img { transform: scale(1.05); }
    .election-card-img-overlay {
      position: absolute; inset: 0;
      background: linear-gradient(to top, rgba(15,23,42,0.9) 0%, transparent 60%);
    }
    .election-card-status {
      position: absolute; top: 16px; right: 16px;
    }
    .election-card-body { padding: 24px; }
    .election-card-title { font-size: 1.15rem; font-weight: 700; margin-bottom: 8px; }
    .election-card-desc  { font-size: 0.85rem; color: var(--text-muted); margin-bottom: 16px; line-height: 1.6; }
    .election-card-meta  {
      display: flex; gap: 16px; margin-bottom: 16px;
      font-size: 0.8rem; color: var(--text-muted);
    }
    .election-card-meta span { display: flex; align-items: center; gap: 5px; }
    .candidate-avatars { display: flex; margin-bottom: 16px; }
    .candidate-avatars img {
      width: 32px; height: 32px;
      border-radius: 50%; object-fit: cover;
      border: 2px solid var(--dark-card);
      margin-right: -8px;
      transition: var(--transition);
    }
    .candidate-avatars img:hover { transform: translateY(-3px); z-index: 1; }
    .candidate-avatars .more {
      width: 32px; height: 32px;
      border-radius: 50%;
      background: var(--glass-bg);
      border: 2px solid var(--glass-border);
      display: flex; align-items: center; justify-content: center;
      font-size: 0.7rem; color: var(--text-muted);
      margin-right: -8px;
    }
    .filter-tabs {
      display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 32px;
    }
    .filter-tab {
      padding: 8px 20px;
      border-radius: 50px;
      border: 1px solid var(--dark-border);
      background: transparent;
      color: var(--text-muted);
      font-size: 0.85rem; font-weight: 600;
      cursor: pointer; transition: var(--transition);
    }
    .filter-tab.active, .filter-tab:hover {
      background: linear-gradient(135deg, #4facfe, #00f2fe);
      color: #0f172a; border-color: transparent;
    }
    .elections-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
      gap: 24px;
    }
    .page-hero {
      padding: 120px 0 60px;
      text-align: center;
      position: relative;
    }
    .page-hero::before {
      content: '';
      position: absolute; inset: 0;
      background: url('https://images.unsplash.com/photo-1540910419892-4a36d2c3266c?w=1600&h=600&fit=crop') center/cover;
      opacity: 0.08;
    }
    .live-dot {
      display: inline-block;
      width: 8px; height: 8px;
      background: #38ef7d;
      border-radius: 50%;
      margin-right: 5px;
      animation: pulse 1.5s ease-in-out infinite;
    }
  </style>
</head>
<body style="background:#0f172a;min-height:100vh;">
<canvas id="particles-canvas"></canvas>

<!-- Navbar -->
<nav class="navbar">
  <a href="/online-voting-system/public/index.php" class="navbar-brand">
    <img src="https://images.unsplash.com/photo-1540910419892-4a36d2c3266c?w=80&h=80&fit=crop"
         style="width:36px;height:36px;border-radius:10px;object-fit:cover;" alt="">
    VoteSecure
  </a>
  <ul class="navbar-nav">
    <li><a href="/online-voting-system/public/index.php">Home</a></li>
    <li><a href="/online-voting-system/public/elections.php" class="active">Elections</a></li>
    <li><a href="/online-voting-system/public/about.php">About Us</a></li>
    <?php if (Auth::isLoggedIn()): ?>
    <li><a href="<?= Auth::isAdmin() ? '/online-voting-system/views/admin/dashboard.php' : '/online-voting-system/views/voter/dashboard.php' ?>" class="btn btn-primary btn-sm">Dashboard</a></li>
    <?php else: ?>
    <li><a href="/online-voting-system/public/login.php" class="btn btn-outline btn-sm">Login</a></li>
    <li><a href="/online-voting-system/public/register.php" class="btn btn-primary btn-sm">Get Started</a></li>
    <?php endif; ?>
  </ul>
</nav>

<!-- Page Hero -->
<div class="page-hero">
  <div class="container" style="position:relative;z-index:1;">
    <p style="color:var(--primary-start);font-size:0.85rem;font-weight:700;text-transform:uppercase;letter-spacing:2px;margin-bottom:12px;">
      <span class="live-dot"></span> Live Elections
    </p>
    <h1 style="margin-bottom:16px;">All <span class="gradient-text">Elections</span></h1>
    <p style="color:var(--text-muted);max-width:500px;margin:0 auto;">
      Browse all active and upcoming elections. Register to cast your secure, anonymous vote.
    </p>
  </div>
</div>

<div class="container" style="padding-bottom:80px;">

  <!-- Filter Tabs -->
  <div class="filter-tabs reveal">
    <button class="filter-tab active" onclick="filterElections('all', this)">All Elections</button>
    <button class="filter-tab" onclick="filterElections('active', this)">🟢 Active</button>
    <button class="filter-tab" onclick="filterElections('upcoming', this)">🔵 Upcoming</button>
    <button class="filter-tab" onclick="filterElections('closed', this)">⚫ Closed</button>
  </div>

  <!-- Elections Grid -->
  <div class="elections-grid" id="elections-grid">
    <?php foreach ($allElections as $i => $e):
      $img = $electionImages[$e['id']] ?? 'https://images.unsplash.com/photo-1494172961521-33799ddd43a5?w=800&h=400&fit=crop';
      $statusColors = ['active' => '#38ef7d', 'upcoming' => '#4facfe', 'closed' => '#94a3b8'];
      $color = $statusColors[$e['status']] ?? '#94a3b8';
    ?>
    <div class="election-card reveal delay-<?= ($i % 3) + 1 ?>" data-status="<?= $e['status'] ?>">
      <div class="election-card-img">
        <img src="<?= $img ?>" alt="<?= htmlspecialchars($e['title']) ?>">
        <div class="election-card-img-overlay"></div>
        <div class="election-card-status">
          <span class="badge badge-<?= $e['status'] ?>">
            <?php if ($e['status'] === 'active'): ?><span class="live-dot"></span><?php endif; ?>
            <?= ucfirst($e['status']) ?>
          </span>
        </div>
      </div>
      <div class="election-card-body">
        <div class="election-card-title"><?= htmlspecialchars($e['title']) ?></div>
        <div class="election-card-desc"><?= htmlspecialchars(substr($e['description'], 0, 100)) ?>...</div>

        <!-- Candidate Avatars -->
        <?php if (!empty($e['candidates'])): ?>
        <div class="candidate-avatars">
          <?php foreach ($e['candidates'] as $c): ?>
          <img src="<?= strpos($c['image'], 'http') === 0 ? $c['image'] : '/online-voting-system/assets/images/candidates/' . $c['image'] ?>"
               onerror="this.src='https://images.unsplash.com/photo-1633332755192-727a05c4013d?w=60&h=60&fit=crop&crop=face'"
               title="<?= htmlspecialchars($c['name']) ?>" alt="">
          <?php endforeach; ?>
          <?php if ($e['total_candidates'] > 3): ?>
          <div class="more">+<?= $e['total_candidates'] - 3 ?></div>
          <?php endif; ?>
          <span style="margin-left:16px;font-size:0.8rem;color:var(--text-muted);align-self:center;">
            <?= $e['total_candidates'] ?> candidates
          </span>
        </div>
        <?php endif; ?>

        <div class="election-card-meta">
          <span>📅 Ends <?= date('M d, Y', strtotime($e['end_date'])) ?></span>
          <span>🗳️ <?= number_format($e['total_votes']) ?> votes</span>
        </div>

        <!-- Progress bar -->
        <div style="margin-bottom:16px;">
          <?php
            $start = strtotime($e['start_date']);
            $end   = strtotime($e['end_date']);
            $now   = time();
            $progress = $end > $start ? min(100, max(0, round(($now - $start) / ($end - $start) * 100))) : 0;
          ?>
          <div style="display:flex;justify-content:space-between;font-size:0.75rem;color:var(--text-muted);margin-bottom:4px;">
            <span>Election Progress</span><span><?= $progress ?>%</span>
          </div>
          <div class="progress">
            <div class="progress-bar" style="width:<?= $progress ?>%;background:linear-gradient(90deg,<?= $color ?>,<?= $color ?>88);"></div>
          </div>
        </div>

        <!-- Action Button -->
        <?php if ($e['status'] === 'active'): ?>
          <?php if (Auth::isLoggedIn()): ?>
            <?php if (!empty($e['has_voted'])): ?>
            <a href="/online-voting-system/public/results.php?election_id=<?= $e['id'] ?>" class="btn btn-outline w-100">📊 View Results</a>
            <?php else: ?>
            <a href="/online-voting-system/views/voter/vote.php?election_id=<?= $e['id'] ?>" class="btn btn-primary w-100">🗳️ Vote Now</a>
            <?php endif; ?>
          <?php else: ?>
          <a href="/online-voting-system/public/register.php" class="btn btn-primary w-100">Register to Vote</a>
          <?php endif; ?>
        <?php elseif ($e['status'] === 'upcoming'): ?>
        <button class="btn btn-outline w-100" disabled style="opacity:0.6;cursor:not-allowed;">⏳ Coming Soon</button>
        <?php else: ?>
        <a href="/online-voting-system/public/results.php?election_id=<?= $e['id'] ?>" class="btn btn-outline w-100">📊 Final Results</a>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Footer -->
<footer>
  <div class="container">
    <div class="footer-bottom" style="border-top:1px solid var(--dark-border);padding-top:24px;">
      <span>© <?= date('Y') ?> VoteSecure. All rights reserved.</span>
      <span>🔒 Secured with end-to-end encryption</span>
    </div>
  </div>
</footer>

<script src="/online-voting-system/assets/js/app.js"></script>
<script>
function filterElections(status, btn) {
  document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.election-card').forEach(card => {
    const show = status === 'all' || card.dataset.status === status;
    card.style.display = show ? '' : 'none';
  });
}
</script>
</body>
</html>
