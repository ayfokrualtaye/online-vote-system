<?php
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/security.php';
require_once __DIR__ . '/../../models/Election.php';
require_once __DIR__ . '/../../models/Candidate.php';
require_once __DIR__ . '/../../models/Vote.php';

Auth::startSession();
Auth::requireLogin('/online-voting-system/public/login.php');

$user       = Auth::currentUser();
$electionId = (int)($_GET['election_id'] ?? 0);
$elections  = (new Election())->getActive();
$voteModel  = new Vote();

if (!$electionId && !empty($elections)) {
    $electionId = $elections[0]['id'];
}

$election   = $electionId ? (new Election())->getById($electionId) : null;
$candidates = $electionId ? (new Candidate())->getByElection($electionId) : [];
$hasVoted   = $electionId ? $voteModel->hasVoted($user['id'], $electionId) : false;

$error   = '';
$success = '';

// Handle vote POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['candidate_id'])) {
    if (!Security::validateCSRF($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request token. Please try again.';
    } else {
        $candidateId = (int)$_POST['candidate_id'];
        $postElectionId = (int)$_POST['election_id'];

        // Re-check election is active
        $el = (new Election())->getById($postElectionId);
        if (!$el || $el['status'] !== 'active') {
            $error = 'This election is not currently active.';
        } else {
            $result = $voteModel->cast($user['id'], $candidateId, $postElectionId);
            if ($result['success']) {
                // Redirect to results
                header('Location: /online-voting-system/public/results.php?election_id=' . $postElectionId . '&voted=1');
                exit;
            } else {
                $error = $result['message'];
            }
        }
    }
    // Refresh state after vote attempt
    $hasVoted = $electionId ? $voteModel->hasVoted($user['id'], $electionId) : false;
}

$csrf = Security::generateCSRF();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vote — VoteSecure</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/online-voting-system/assets/css/style.css">
  <link rel="stylesheet" href="/online-voting-system/assets/css/animations.css">
  <link rel="stylesheet" href="/online-voting-system/assets/css/themes.css">
</head>
<body class="theme-voting">
<canvas id="particles-canvas"></canvas>

<?php include __DIR__ . '/partials/navbar.php'; ?>

<div style="padding-top:90px;" class="container section">

  <?php if ($error): ?>
  <div class="alert alert-danger mb-3">⚠️ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <!-- Election Selector -->
  <?php if (count($elections) > 1): ?>
  <div class="mb-3">
    <select class="form-control" style="max-width:350px;"
            onchange="window.location.href='?election_id='+this.value">
      <?php foreach ($elections as $e): ?>
      <option value="<?= $e['id'] ?>" <?= $e['id'] == $electionId ? 'selected' : '' ?>>
        <?= htmlspecialchars($e['title']) ?>
      </option>
      <?php endforeach; ?>
    </select>
  </div>
  <?php endif; ?>

  <?php if (!$election): ?>
  <div class="text-center" style="padding:80px 20px;">
    <div style="font-size:4rem;margin-bottom:16px;">🗳️</div>
    <h2>No Active Elections</h2>
    <p>There are no active elections at this time.</p>
    <a href="dashboard.php" class="btn btn-outline mt-3">← Back to Dashboard</a>
  </div>

  <?php elseif ($hasVoted): ?>
  <div class="success-animation reveal text-center">
    <div class="success-circle" style="margin:0 auto 20px;">✅</div>
    <h2 class="gradient-text">You've Already Voted!</h2>
    <p style="margin:12px 0 24px;">Your vote for <strong><?= htmlspecialchars($election['title']) ?></strong> has been recorded.</p>
    <a href="/online-voting-system/public/results.php?election_id=<?= $electionId ?>" class="btn btn-primary btn-lg">View Results</a>
  </div>

  <?php else: ?>
  <!-- Voting Header -->
  <div class="voting-header reveal">
    <p style="color:var(--primary-start);font-size:0.85rem;font-weight:700;text-transform:uppercase;letter-spacing:2px;margin-bottom:8px;">
      🗳️ Active Election
    </p>
    <h2><?= htmlspecialchars($election['title']) ?></h2>
    <p><?= htmlspecialchars($election['description']) ?></p>
    <p style="margin-top:8px;font-size:0.85rem;">
      Ends: <strong style="color:var(--text-primary);"><?= date('M d, Y', strtotime($election['end_date'])) ?></strong>
    </p>
  </div>

  <p class="text-center mb-3" style="color:var(--text-muted);">
    Click a candidate to select, then click "Cast My Vote"
  </p>

  <!-- Vote Form -->
  <form method="POST" action="" id="vote-form">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    <input type="hidden" name="election_id" value="<?= $electionId ?>">
    <input type="hidden" name="candidate_id" id="selected-candidate-id" value="">

    <!-- Candidates Grid -->
    <div class="candidates-grid reveal">
      <?php foreach ($candidates as $c): ?>
      <div class="candidate-card" data-candidate-id="<?= $c['id'] ?>"
           onclick="selectCandidate(<?= $c['id'] ?>, '<?= htmlspecialchars(addslashes($c['name'])) ?>')">
        <div class="vote-check">✓</div>
        <img class="candidate-avatar"
             src="<?= strpos($c['image'], 'http') === 0 ? $c['image'] : '/online-voting-system/assets/images/candidates/' . htmlspecialchars($c['image']) ?>"
             onerror="this.src='https://images.unsplash.com/photo-1633332755192-727a05c4013d?w=200&h=200&fit=crop&crop=face'"
             alt="<?= htmlspecialchars($c['name']) ?>">
        <div class="candidate-name"><?= htmlspecialchars($c['name']) ?></div>
        <div class="candidate-party"><?= htmlspecialchars($c['party']) ?></div>
        <div class="candidate-bio"><?= htmlspecialchars($c['bio']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Vote Button -->
    <div class="text-center mt-4">
      <button type="button" id="vote-btn" class="btn btn-primary btn-lg" disabled
              onclick="Modal.open('confirm-modal')">
        🗳️ Cast My Vote
      </button>
      <p class="mt-2" style="font-size:0.85rem;color:var(--text-muted);">
        🔒 Your vote is anonymous and cannot be changed after submission
      </p>
    </div>
  </form>
  <?php endif; ?>
</div>

<!-- Confirmation Modal -->
<div class="modal-overlay" id="confirm-modal">
  <div class="modal">
    <div class="modal-header">
      <h3>Confirm Your Vote</h3>
      <button class="modal-close" onclick="Modal.close('confirm-modal')">✕</button>
    </div>
    <div class="text-center" style="padding:20px 0;">
      <div style="font-size:3rem;margin-bottom:16px;">🗳️</div>
      <p style="margin-bottom:8px;">You are voting for:</p>
      <h3 class="gradient-text" id="confirm-candidate-name"></h3>
      <p class="mt-2" style="font-size:0.85rem;">This action cannot be undone.</p>
    </div>
    <div style="display:flex;gap:12px;margin-top:24px;">
      <button type="button" class="btn btn-outline w-100" onclick="Modal.close('confirm-modal')">Cancel</button>
      <button type="button" class="btn btn-primary w-100" onclick="submitVote()">
        ✅ Confirm Vote
      </button>
    </div>
  </div>
</div>

<script src="/online-voting-system/assets/js/app.js"></script>
<script>
function selectCandidate(id, name) {
  document.querySelectorAll('.candidate-card').forEach(c => c.classList.remove('selected'));
  const card = document.querySelector('[data-candidate-id="' + id + '"]');
  if (card) card.classList.add('selected');
  document.getElementById('selected-candidate-id').value = id;
  document.getElementById('vote-btn').disabled = false;
}

function submitVote() {
  const candidateId = document.getElementById('selected-candidate-id').value;
  if (!candidateId) { Toast.error('Please select a candidate.'); return; }
  // Update confirm modal name
  document.getElementById('confirm-candidate-name').textContent =
    document.querySelector('.candidate-card.selected .candidate-name')?.textContent || '';
  // Submit the form directly
  document.getElementById('vote-form').submit();
}
</script>
</body>
</html>
