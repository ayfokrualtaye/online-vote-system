<?php
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/security.php';
require_once __DIR__ . '/../../models/Candidate.php';
require_once __DIR__ . '/../../models/Election.php';

Auth::startSession();
Auth::requireAdmin('/online-voting-system/public/login.php');

$candidateModel = new Candidate();
$electionModel  = new Election();
$message = '';
$csrf = Security::generateCSRF();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::validateCSRF($_POST['csrf_token'] ?? '')) {
        $message = ['type' => 'danger', 'text' => 'Invalid request token.'];
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'create') {
            $image = 'default.png';
            if (!empty($_FILES['image']['name'])) {
                $ext   = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','webp'];
                if (in_array($ext, $allowed) && $_FILES['image']['size'] < 2097152) {
                    $image = uniqid('candidate_') . '.' . $ext;
                    move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../../assets/images/candidates/' . $image);
                }
            }
            $id = $candidateModel->create(
                Security::sanitize($_POST['name'] ?? ''),
                Security::sanitize($_POST['party'] ?? ''),
                Security::sanitize($_POST['bio'] ?? ''),
                $image,
                (int)($_POST['election_id'] ?? 0)
            );
            $message = $id
                ? ['type' => 'success', 'text' => 'Candidate added successfully.']
                : ['type' => 'danger',  'text' => 'Failed to add candidate.'];
        }

        if ($action === 'delete') {
            $candidateModel->delete((int)$_POST['id']);
            $message = ['type' => 'success', 'text' => 'Candidate deleted.'];
        }
    }
}

$elections  = $electionModel->getAll();
$filterElection = (int)($_GET['election_id'] ?? ($elections[0]['id'] ?? 0));
$candidates = $filterElection ? $candidateModel->getByElection($filterElection) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Candidates — VoteSecure</title>
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
      <h2>Manage <span class="gradient-text">Candidates</span></h2>
      <p>Add and manage election candidates</p>
    </div>
    <button class="btn btn-primary" onclick="Modal.open('create-modal')">+ Add Candidate</button>
  </div>

  <?php if ($message): ?>
  <div class="alert alert-<?= $message['type'] ?>"><?= $message['text'] ?></div>
  <?php endif; ?>

  <!-- Filter by election -->
  <div class="mb-3">
    <select class="form-control" style="max-width:300px;"
            onchange="window.location.href='candidates.php?election_id='+this.value">
      <?php foreach ($elections as $e): ?>
      <option value="<?= $e['id'] ?>" <?= $e['id'] == $filterElection ? 'selected' : '' ?>>
        <?= htmlspecialchars($e['title']) ?>
      </option>
      <?php endforeach; ?>
    </select>
  </div>

  <!-- Candidates Grid -->
  <div class="candidates-grid reveal">
    <?php foreach ($candidates as $c): ?>
    <div class="candidate-card" style="cursor:default;">
      <img class="candidate-avatar"
           src="<?= strpos($c['image'], 'http') === 0 ? $c['image'] : '../../assets/images/candidates/' . htmlspecialchars($c['image']) ?>"
           onerror="this.src='https://images.unsplash.com/photo-1633332755192-727a05c4013d?w=200&h=200&fit=crop&crop=face'"
           alt="<?= htmlspecialchars($c['name']) ?>">
      <div class="candidate-name"><?= htmlspecialchars($c['name']) ?></div>
      <div class="candidate-party"><?= htmlspecialchars($c['party']) ?></div>
      <div class="candidate-bio"><?= htmlspecialchars($c['bio']) ?></div>
      <div style="margin-top:12px;font-size:0.85rem;color:var(--primary-start);">
        🗳️ <?= $c['vote_count'] ?> votes
      </div>
      <form method="POST" style="margin-top:12px;"
            onsubmit="return confirm('Delete this candidate?')">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" value="<?= $c['id'] ?>">
        <button type="submit" class="btn btn-danger btn-sm w-100">Delete</button>
      </form>
    </div>
    <?php endforeach; ?>
    <?php if (empty($candidates)): ?>
    <div style="grid-column:1/-1;text-align:center;padding:60px 20px;">
      <div style="font-size:3rem;margin-bottom:16px;">👤</div>
      <h3>No Candidates Yet</h3>
      <p>Add candidates to this election.</p>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Create Modal -->
<div class="modal-overlay" id="create-modal">
  <div class="modal">
    <div class="modal-header">
      <h3>Add Candidate</h3>
      <button class="modal-close" onclick="Modal.close('create-modal')">✕</button>
    </div>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
      <input type="hidden" name="action" value="create">
      <div class="form-group">
        <label class="form-label">Election</label>
        <select name="election_id" class="form-control" required>
          <?php foreach ($elections as $e): ?>
          <option value="<?= $e['id'] ?>" <?= $e['id'] == $filterElection ? 'selected' : '' ?>>
            <?= htmlspecialchars($e['title']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control" required>
      </div>
      <div class="form-group">
        <label class="form-label">Party / Affiliation</label>
        <input type="text" name="party" class="form-control">
      </div>
      <div class="form-group">
        <label class="form-label">Bio</label>
        <textarea name="bio" class="form-control" rows="3"></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Photo (optional, max 2MB)</label>
        <input type="file" name="image" class="form-control" accept="image/*">
      </div>
      <button type="submit" class="btn btn-primary w-100">Add Candidate</button>
    </form>
  </div>
</div>

<script src="/online-voting-system/assets/js/app.js"></script>
</body>
</html>
