<?php
$user    = Auth::currentUser();
$isAdmin = Auth::isAdmin();
$base    = '/online-voting-system';
?>
<nav class="navbar">
  <a href="<?= $base ?>/<?= $isAdmin ? 'views/admin/dashboard.php' : 'views/voter/dashboard.php' ?>" class="navbar-brand">
    <div class="logo-icon">🗳️</div>
    VoteSecure
  </a>
  <ul class="navbar-nav">
    <?php if ($isAdmin): ?>
    <li><a href="<?= $base ?>/views/admin/dashboard.php">Dashboard</a></li>
    <li><a href="<?= $base ?>/views/admin/elections.php">Elections</a></li>
    <li><a href="<?= $base ?>/views/admin/candidates.php">Candidates</a></li>
    <li><a href="<?= $base ?>/views/admin/users.php">Users</a></li>
    <li><a href="<?= $base ?>/views/admin/results.php">Results</a></li>
    <?php else: ?>
    <li><a href="<?= $base ?>/views/voter/dashboard.php">Dashboard</a></li>
    <li><a href="<?= $base ?>/public/elections.php">Elections</a></li>
    <li><a href="<?= $base ?>/views/voter/vote.php">Vote</a></li>
    <li><a href="<?= $base ?>/views/voter/apply.php">Run for Office</a></li>
    <li><a href="<?= $base ?>/views/voter/history.php">My History</a></li>
    <li><a href="<?= $base ?>/public/results.php">Results</a></li>
    <?php endif; ?>
    <li>
      <a href="<?= $base ?>/public/logout.php" class="btn btn-danger btn-sm">Logout</a>
    </li>
  </ul>
</nav>
