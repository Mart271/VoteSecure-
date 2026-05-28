<?php
// voter/results.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Services/SessionService.php';
require_once __DIR__ . '/../src/Services/AuthService.php';
require_once __DIR__ . '/../src/Controllers/VoterController.php';
require_once __DIR__ . '/../src/Models/Election.php';
SessionService::start(SessionService::VOTER_SESSION);

$auth = new AuthService();
$auth->requireRole('voter');

$ctrl       = new VoterController();
$electionId = (int) ($_GET['election_id'] ?? 0);

if ($electionId) {
    $data = $ctrl->getResults($electionId);
    if (isset($data['error'])) {
        $_SESSION['flash_error'] = $data['error'];
        header('Location: ' . APP_URL . '/voter/portal.php'); exit;
    }
} else {
    // Show list of published elections
    $data = ['elections_list' => (new Election())->getPublishedElections()];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Results — VoteSecure</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Mono:wght@300;400;500&family=Newsreader:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= APP_URL ?>/public/css/style.css">
</head>
<body>
<nav class="topbar">
  <div class="topbar-logo"><div class="topbar-logo-icon">🗳️</div> VoteSecure</div>
  <div class="topbar-nav">
    <a href="<?= APP_URL ?>/voter/portal.php">My Elections</a>
    <a href="<?= APP_URL ?>/voter/results.php" class="active">Results</a>
  </div>
  <div class="topbar-right">
    <a href="<?= APP_URL ?>/logout.php?session=voter" class="btn btn-outline btn-sm">Logout</a>
  </div>
</nav>
<div class="app-content">
  <div class="page-header">
    <div><div class="page-title">Election Results</div><div class="page-sub">Official published results</div></div>
  </div>

  <?php if (!empty($data['elections_list'])): ?>
    <!-- List published elections -->
    <div class="voter-election-list">
      <?php foreach ($data['elections_list'] as $el): ?>
        <div class="voter-election-item">
          <div>
            <div class="vei-title"><?= htmlspecialchars($el['title']) ?></div>
            <div class="vei-meta">📅 <?= substr($el['start_datetime'],0,10) ?> &middot; <span class="badge badge-<?= $el['status'] ?>"><?= $el['status'] ?></span></div>
          </div>
          <a href="?election_id=<?= $el['election_id'] ?>" class="btn btn-primary btn-sm">View Results →</a>
        </div>
      <?php endforeach; ?>
    </div>

  <?php elseif (!empty($data['election'])): ?>
    <?php
    $el      = $data['election'];
    $turnout = $data['turnout'];
    $byPos   = [];
    foreach ($data['results'] as $r) { $byPos[$r['position_id']][] = $r; }
    ?>
    <div class="card" style="margin-bottom:20px">
      <div class="card-body" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px">
        <div>
          <div style="font-family:'Syne',sans-serif;font-size:22px;font-weight:800;color:var(--navy)"><?= htmlspecialchars($el['title']) ?></div>
          <div style="margin-top:6px"><span class="badge badge-<?= $el['status'] ?>"><?= $el['status'] ?></span></div>
        </div>
        <div style="display:flex;gap:28px">
          <div style="text-align:center"><div class="big-num"><?= $turnout['total'] ?></div><div class="big-lbl">Registered</div></div>
          <div style="text-align:center"><div class="big-num" style="color:var(--blue)"><?= $turnout['voted'] ?></div><div class="big-lbl">Voted</div></div>
          <div style="text-align:center"><div class="big-num" style="color:var(--gold)"><?= $turnout['pct'] ?>%</div><div class="big-lbl">Turnout</div></div>
        </div>
      </div>
    </div>

    <?php foreach ($data['positions'] as $pos):
      $posResults = $byPos[$pos['position_id']] ?? [];
      $maxVotes   = max(array_column($posResults, 'vote_count') ?: [1]);
      $totalVotes = array_sum(array_column($posResults, 'vote_count'));
    ?>
    <div class="card results-card" style="margin-bottom:14px">
      <div class="card-head"><h3><?= htmlspecialchars($pos['position_name']) ?></h3></div>
      <div class="card-body">
        <?php foreach ($posResults as $i => $r):
          $pct  = $totalVotes > 0 ? round($r['vote_count'] / $totalVotes * 100, 1) : 0;
          $barW = $maxVotes > 0 ? round($r['vote_count'] / $maxVotes * 100) : 0;
        ?>
        <div class="result-row">
          <div class="result-rank <?= $i === 0 ? 'first' : '' ?>"><?= $i + 1 ?></div>
          <div style="min-width:180px">
            <div class="result-name"><?= htmlspecialchars($r['full_name']) ?></div>
            <div class="result-party"><?= htmlspecialchars($r['party_affiliation'] ?: 'Independent') ?></div>
          </div>
          <div class="result-bar-wrap">
            <div class="result-bar"><div class="result-bar-fill <?= $i===0?'first':'' ?>" style="width:<?= $barW ?>%"></div></div>
          </div>
          <div class="result-count"><?= $r['vote_count'] ?> votes</div>
          <div class="result-pct"><?= $pct ?>%</div>
          <?php if ($i === 0 && $totalVotes > 0): ?><div style="font-size:20px;margin-left:8px">🏆</div><?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>

    <div style="margin-top:16px"><a href="<?= APP_URL ?>/voter/results.php" class="btn btn-outline btn-sm">← Back to All Results</a></div>

  <?php else: ?>
    <div class="empty-state">No published results available yet.</div>
  <?php endif; ?>
</div>
<footer class="app-footer"><span>VoteSecure <?= APP_VERSION ?></span><span><?= date('Y') ?></span></footer>
<script src="<?= APP_URL ?>/public/js/app.js"></script>
</body>
</html>