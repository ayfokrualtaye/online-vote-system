<?php
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/security.php';
require_once __DIR__ . '/../../models/Election.php';

Auth::startSession();
Auth::requireAdmin('/online-voting-system/public/login.php');

$electionModel = new Election();
$message = '';
$csrf = Security::generateCSRF();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::validateCSRF($_POST['csrf_token'] ?? '')) {
        $message = ['type' => 'danger', 'text' => 'Invalid request token.'];
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'create') {
            $id = $electionModel->create(
                Security::sanitize($_POST['title'] ?? ''),
                Security::sanitize($_POST['description'] ?? ''),
                $_POST['status'] ?? 'upcoming',
                $_POST['start_date'] ?? '',
                $_POST['end_date'] ?? ''
            );
            $message = $id
                ? ['type' => 'success', 'text' => 'Election created successfully.']
                : ['type' => 'danger',  'text' => 'Failed to create election.'];
        }

        if ($action === 'update') {
            $ok = $electionModel->update(
                (int)$_POST['id'],
                Security::sanitize($_POST['title'] ?? ''),
                Security::sanitize($_POST['description'] ?? ''),
                $_POST['status'] ?? 'upcoming',
                $_POST['start_date'] ?? '',
                $_POST['end_date'] ?? ''
            );
            $message = $ok
                ? ['type' => 'success', 'text' => 'Election updated.']
                : ['type' => 'danger',  'text' => 'Update failed.'];
        }

        if ($action === 'delete') {
            $electionModel->delete((int)$_POST['id']);
            $message = ['type' => 'success', 'text' => 'Election deleted.'];
        }
    }
}

$elections = $electionModel->getAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Elections — VoteSecure</title>
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
      <h2>Manage <span class="gradient-text">Elections</span></h2>
      <p>Create and manage voting elections</p>
    </div>
    <button class="btn btn-primary" onclick="Modal.open('create-modal')">+ New Election</button>
  </div>

  <?php if ($message): ?>
  <div class="alert alert-<?= $message['type'] ?> reveal"><?= $message['text'] ?></div>
  <?php endif; ?>

  <div class="table-wrapper reveal">
    <div class="table-header">
      <h3>All Elections (<?= count($elections) ?>)</h3>
    </div>
    <table>
      <thead>
        <tr>
          <th>#</th><th>Title</th><th>Status</th><th>Start</th><th>End</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($elections as $e): ?>
        <tr>
          <td><?= $e['id'] ?></td>
          <td>
            <strong><?= htmlspecialchars($e['title']) ?></strong>
            <br><small style="color:var(--text-muted);"><?= htmlspecialchars(substr($e['description'], 0, 50)) ?>...</small>
          </td>
          <td><span class="badge badge-<?= $e['status'] ?>"><?= ucfirst($e['status']) ?></span></td>
          <td><?= date('M d, Y', strtotime($e['start_date'])) ?></td>
          <td><?= date('M d, Y', strtotime($e['end_date'])) ?></td>
          <td>
            <button class="btn btn-outline btn-sm"
                    onclick="editElection(<?= htmlspecialchars(json_encode($e)) ?>)">Edit</button>
            <form method="POST" style="display:inline;"
                  onsubmit="return confirm('Delete this election and all its votes?')">
              <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $e['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Create Modal -->
<div class="modal-overlay" id="create-modal">
  <div class="modal">
    <div class="modal-header">
      <h3>Create Election</h3>
      <button class="modal-close" onclick="Modal.close('create-modal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
      <input type="hidden" name="action" value="create">
      <div class="form-group">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" required>
      </div>
      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control"></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Status</label>
        <select name="status" class="form-control">
          <option value="upcoming">Upcoming</option>
          <option value="active">Active</option>
          <option value="closed">Closed</option>
        </select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="form-group">
          <label class="form-label">Start Date</label>
          <input type="datetime-local" name="start_date" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">End Date</label>
          <input type="datetime-local" name="end_date" class="form-control" required>
        </div>
      </div>
      <button type="submit" class="btn btn-primary w-100">Create Election</button>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="edit-modal">
  <div class="modal">
    <div class="modal-header">
      <h3>Edit Election</h3>
      <button class="modal-close" onclick="Modal.close('edit-modal')">✕</button>
    </div>
    <form method="POST" id="edit-form">
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" id="edit-id">
      <div class="form-group">
        <label class="form-label">Title</label>
        <input type="text" name="title" id="edit-title" class="form-control" required>
      </div>
      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="description" id="edit-description" class="form-control"></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Status</label>
        <select name="status" id="edit-status" class="form-control">
          <option value="upcoming">Upcoming</option>
          <option value="active">Active</option>
          <option value="closed">Closed</option>
        </select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="form-group">
          <label class="form-label">Start Date</label>
          <input type="datetime-local" name="start_date" id="edit-start" class="form-control">
        </div>
        <div class="form-group">
          <label class="form-label">End Date</label>
          <input type="datetime-local" name="end_date" id="edit-end" class="form-control">
        </div>
      </div>
      <button type="submit" class="btn btn-primary w-100">Update Election</button>
    </form>
  </div>
</div>

<script src="/online-voting-system/assets/js/app.js"></script>
<script>
function editElection(e) {
  document.getElementById('edit-id').value = e.id;
  document.getElementById('edit-title').value = e.title;
  document.getElementById('edit-description').value = e.description;
  document.getElementById('edit-status').value = e.status;
  document.getElementById('edit-start').value = e.start_date?.replace(' ', 'T').slice(0,16);
  document.getElementById('edit-end').value = e.end_date?.replace(' ', 'T').slice(0,16);
  Modal.open('edit-modal');
}
</script>
</body>
</html>
