<?php
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/security.php';
require_once __DIR__ . '/../../models/Election.php';
require_once __DIR__ . '/../../models/Application.php';

Auth::startSession();
Auth::requireLogin('/online-voting-system/public/login.php');

$user    = Auth::currentUser();
$appModel = new Application();
$elections = (new Election())->getActive();
$myApps    = $appModel->getByUser($user['id']);

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::validateCSRF($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request token.';
    } else {
        $electionId = (int)($_POST['election_id'] ?? 0);
        $party      = Security::sanitize($_POST['party'] ?? '');
        $bio        = Security::sanitize($_POST['bio'] ?? '');
        $manifesto  = Security::sanitize($_POST['manifesto'] ?? '');

        if (!$electionId || !$party || !$bio || !$manifesto) {
            $error = 'All fields are required.';
        } else {
            $result = $appModel->apply($user['id'], $electionId, $party, $bio, $manifesto);
            if ($result['success']) {
                $success = $result['message'];
                $myApps  = $appModel->getByUser($user['id']); // refresh
            } else {
                $error = $result['message'];
            }
        }
    }
}

$csrf = Security::generateCSRF();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Apply as Candidate — VoteSecure</title>
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
      <h2>Apply as <span class="gradient-text">Candidate</span></h2>
      <p>Submit your application to run in an active election</p>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:32px;align-items:start;">

    <!-- Application Form -->
    <div class="card reveal">
      <h3 style="margin-bottom:20px;">📝 New Application</h3>

      <?php if ($error):   ?><div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>

      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

        <div class="form-group">
          <label class="form-label">Select Election</label>
          <select name="election_id" class="form-control" required>
            <option value="">-- Choose an election --</option>
            <?php foreach ($elections as $e): ?>
            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['title']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Party / Affiliation</label>
          <input type="text" name="party" class="form-control" placeholder="e.g. Progressive Party" required>
        </div>

        <div class="form-group">
          <label class="form-label">Short Bio</label>
          <textarea name="bio" class="form-control" rows="3"
                    placeholder="Brief description of your background and qualifications..." required></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Manifesto / Campaign Statement</label>
          <textarea name="manifesto" class="form-control" rows="5"
                    placeholder="What will you do if elected? What are your key promises and goals?" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary w-100">Submit Application</button>
      </form>
    </div>

    <!-- My Applications -->
    <div class="reveal">
      <h3 style="margin-bottom:20px;">📋 My Applications</h3>
      <?php if (empty($myApps)): ?>
      <div class="card text-center" style="padding:40px;">
        <div style="font-size:3rem;margin-bottom:12px;">📭</div>
        <p>No applications yet. Submit your first one!</p>
      </div>
      <?php else: ?>
      <?php foreach ($myApps as $app):
        $statusStyle = [
          'pending'  => ['badge-upcoming', '⏳'],
          'approved' => ['badge-active',   '✅'],
          'rejected' => ['badge-closed',   '❌'],
        ][$app['status']] ?? ['badge-closed', '?'];
      ?>
      <div class="card card-dark mb-3" style="padding:20px;">
        <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:12px;">
          <div>
            <div style="font-weight:700;margin-bottom:4px;"><?= htmlspecialchars($app['election_title']) ?></div>
            <div style="font-size:0.85rem;color:var(--primary-start);"><?= htmlspecialchars($app['party']) ?></div>
          </div>
          <span class="badge <?= $statusStyle[0] ?>"><?= $statusStyle[1] ?> <?= ucfirst($app['status']) ?></span>
        </div>
        <p style="font-size:0.85rem;margin-bottom:8px;"><?= htmlspecialchars(substr($app['bio'], 0, 100)) ?>...</p>
        <?php if ($app['admin_note']): ?>
        <div style="background:rgba(255,255,255,0.04);border-radius:8px;padding:10px;font-size:0.8rem;color:var(--text-muted);">
          <strong>Admin note:</strong> <?= htmlspecialchars($app['admin_note']) ?>
        </div>
        <?php endif; ?>
        <div style="font-size:0.75rem;color:var(--text-muted);margin-top:8px;">
          Applied: <?= date('M d, Y', strtotime($app['applied_at'])) ?>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="/online-voting-system/assets/js/app.js"></script>
</body>
</html>
