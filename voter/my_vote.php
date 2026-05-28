<?php
// voter/my_vote.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Services/SessionService.php';
require_once __DIR__ . '/../src/Services/AuthService.php';
require_once __DIR__ . '/../src/Controllers/VoterController.php';
SessionService::start(SessionService::VOTER_SESSION);

$auth = new AuthService();
$auth->requireRole('voter');

$ctrl       = new VoterController();
$electionId = (int) ($_GET['election_id'] ?? 0);

if (!$electionId) {
    header('Location: ' . APP_URL . '/voter/portal.php'); exit;
}

$data = $ctrl->getMyBallot($electionId);
if (!empty($data['error'])) {
    $_SESSION['flash_error'] = $data['error'];
    header('Location: ' . APP_URL . '/voter/portal.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Vote — VoteSecure</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Mono:wght@300;400;500&family=Newsreader:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= APP_URL ?>/public/css/style.css">
</head>
<body>

<nav class="topbar">
  <div class="topbar-logo"><div class="topbar-logo-icon">🗳️</div> VoteSecure</div>
  <div class="topbar-nav">
    <a href="<?= APP_URL ?>/voter/portal.php">My Elections</a>
    <a href="<?= APP_URL ?>/voter/results.php">Results</a>
    <a href="<?= APP_URL ?>/voter/my_vote.php?election_id=<?= $data['election']['election_id'] ?>" class="active">My Vote</a>
  </div>
  <div class="topbar-right">
    <a href="<?= APP_URL ?>/logout.php?session=voter" class="btn btn-outline btn-sm">Logout</a>
  </div>
</nav>

<div class="app-content">
  <div class="page-header">
    <div>
      <div class="page-title">My Vote</div>
      <div class="page-sub">Review the candidates you selected for this election</div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:16px">
        <div>
          <div class="page-title" style="font-size:22px;"><?= htmlspecialchars($data['election']['title']) ?></div>
          <div class="page-sub">Submitted: <?= htmlspecialchars($data['ballot']['submitted_at']) ?></div>
        </div>
        <div style="text-align:right">
          <div class="tag">Voter Code: <strong><?= htmlspecialchars($data['voter']['voter_code']) ?></strong></div>
          <div class="tag">Ballot #: <strong>#<?= htmlspecialchars($data['ballot']['ballot_id']) ?></strong></div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <?php if (!empty($data['choices'])): ?>
        <?php foreach ($data['choices'] as $choice): ?>
          <div class="vote-record">
            <div class="vote-position"><?= htmlspecialchars($choice['position_name']) ?></div>
            <div class="vote-candidate">
              <strong><?= htmlspecialchars($choice['full_name']) ?></strong>
              <span><?= htmlspecialchars($choice['party_affiliation'] ?: 'Independent') ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state">No ballot choices were found for this vote.</div>
      <?php endif; ?>
    </div>
  </div>

  <a href="<?= APP_URL ?>/voter/portal.php" class="btn btn-outline">← Back to My Elections</a>
</div>

<footer class="app-footer">
  <span>VoteSecure <?= APP_VERSION ?> &nbsp;|&nbsp; AI-Integrated Election System</span>
  <span><?= date('Y') ?></span>
</footer>
<script src="<?= APP_URL ?>/public/js/app.js"></script>
</body>
</html>
