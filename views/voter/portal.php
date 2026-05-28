<?php
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    header('Location: ' . (defined('APP_URL') ? APP_URL . '/index.php' : '/index.php'));
    exit;
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Elections — VoteSecure</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Mono:wght@300;400;500&family=Newsreader:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= APP_URL ?>/public/css/style.css">
</head>
<body>

<nav class="topbar">
  <div class="topbar-logo">
    <div class="topbar-logo-icon">🗳️</div> VoteSecure
  </div>
  <div class="topbar-nav">
    <a href="<?= APP_URL ?>/voter/portal.php" class="active">My Elections</a>
    <a href="<?= APP_URL ?>/voter/results.php">Results</a>
    <a href="<?= APP_URL ?>/voter/profile.php">Profile</a>
  </div>
  <div class="topbar-right">
    <div class="user-chip">
      <div class="user-avatar">
        <?php if (!empty($_SESSION['avatar'])): ?>
          <img src="<?= APP_URL ?>/public/uploads/avatars/<?= htmlspecialchars($_SESSION['avatar']) ?>" alt="avatar" style="height:40px;width:40px;object-fit:cover;border-radius:50%;">
        <?php else: ?>
          📷
        <?php endif; ?>
      </div>
      <div class="user-info">
        <div class="name"><?= htmlspecialchars($_SESSION['full_name'] ?? '') ?></div>
        <div class="role-badge">Voter</div>
      </div>
    </div>
    <a href="<?= APP_URL ?>/logout.php?session=voter" class="btn btn-outline btn-sm">Logout</a>
  </div>
</nav>

<div class="app-content">

  <div class="page-header">
    <div>
      <div class="page-title">My Elections</div>
      <div class="page-sub">Elections you are registered to participate in</div>
    </div>
  </div>

  <?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
    <?php unset($_SESSION['flash_success']); ?>
  <?php endif; ?>

  <div class="voter-election-list">
    <?php foreach ($data['elections'] as $v): ?>
      <div class="voter-election-item">
        <div>
          <div class="vei-title"><?= htmlspecialchars($v['election_title']) ?></div>
          <div class="vei-meta">
            Voter Code: <span class="voter-code"><?= htmlspecialchars($v['voter_code'] ?? '—') ?></span>
            &nbsp;&middot;&nbsp;
            <?= htmlspecialchars($v['start_datetime']) ?> &rarr; <?= htmlspecialchars($v['end_datetime']) ?>
          </div>
          <?php if ($v['has_voted']): ?>
            <div class="vei-voted">✅ You voted on <?= htmlspecialchars($v['voted_at'] ?? '') ?></div>
          <?php endif; ?>
        </div>
        <div style="display:flex;gap:10px;align-items:center">
          <span class="badge badge-<?= $v['election_status'] ?>"><?= $v['election_status'] ?></span>
          <?php if ($v['election_status'] === 'active' && !$v['has_voted']): ?>
            <?php if (!empty($v['is_registered'])): ?>
              <a href="<?= APP_URL ?>/voter/ballot.php?election_id=<?= $v['election_id'] ?>" class="btn btn-primary">Cast Ballot →</a>
            <?php else: ?>
              <span style="font-family:'DM Mono',monospace;font-size:12px;color:var(--muted)">Not registered for this election</span>
            <?php endif; ?>
          <?php elseif ($v['election_status'] === 'active' && $v['has_voted']): ?>
            <a href="<?= APP_URL ?>/voter/my_vote.php?election_id=<?= $v['election_id'] ?>" class="btn btn-outline btn-sm">View My Vote</a>
          <?php elseif ($v['election_status'] === 'published' || ($v['election_status'] === 'closed')): ?>
            <a href="<?= APP_URL ?>/voter/results.php?election_id=<?= $v['election_id'] ?>" class="btn btn-outline btn-sm">View Results</a>
            <?php if (!empty($v['has_voted'])): ?>
              <a href="<?= APP_URL ?>/voter/my_vote.php?election_id=<?= $v['election_id'] ?>" class="btn btn-primary btn-sm">View My Vote</a>
            <?php endif; ?>
          <?php elseif ($v['has_voted']): ?>
            <a href="<?= APP_URL ?>/voter/my_vote.php?election_id=<?= $v['election_id'] ?>" class="btn btn-outline btn-sm">View My Vote</a>
          <?php else: ?>
            <span style="font-family:'DM Mono',monospace;font-size:12px;color:var(--muted)">Not yet open</span>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (empty($data['elections'])): ?>
      <div class="empty-state">You are not registered in any elections yet. Contact your Election Administrator.</div>
    <?php endif; ?>
  </div>

</div>

<footer class="app-footer">
  <span>VoteSecure <?= APP_VERSION ?> &nbsp;|&nbsp; AI-Integrated Election System</span>
  <span><?= date('Y') ?></span>
</footer>
<script src="<?= APP_URL ?>/public/js/app.js"></script>
</body>
</html>