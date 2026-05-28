<?php
// views/admin/results.php
$pageTitle  = 'Election Results';
$activePage = 'results';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
  <div>
    <div class="page-title">Election Results</div>
    <div class="page-sub">Official vote tallies and result publication</div>
  </div>
</div>

<?php if (empty($elections)): ?>
  <div class="empty-state">No elections with results available. Elections must be Active, Closed, or Published.</div>
<?php endif; ?>

<?php foreach ($elections as $el):
  $elId    = $el['election_id'];
  $turnout = $el['turnout'];
  $results = $el['results'];
  $posList = $el['positions'];
  // group results by position
  $byPos = [];
  foreach ($results as $r) { $byPos[$r['position_id']][] = $r; }
?>

<div style="margin-bottom:36px">

  <!-- Election Header Card -->
  <div class="card" style="margin-bottom:16px">
    <div class="card-body" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px">
      <div>
        <div style="font-family:'Syne',sans-serif;font-size:22px;font-weight:800;color:var(--navy)"><?= htmlspecialchars($el['title']) ?></div>
        <div style="margin-top:6px;display:flex;align-items:center;gap:12px">
          <span class="badge badge-<?= $el['status'] ?>"><?= $el['status'] ?></span>
          <span style="font-family:'DM Mono',monospace;font-size:12px;color:var(--muted)">📅 <?= substr($el['start_datetime'], 0, 10) ?></span>
        </div>
      </div>
      <div style="display:flex;gap:28px;align-items:center">
        <div style="text-align:center">
          <div class="big-num"><?= $turnout['total'] ?></div>
          <div class="big-lbl">Registered</div>
        </div>
        <div style="text-align:center">
          <div class="big-num" style="color:var(--blue)"><?= $turnout['voted'] ?></div>
          <div class="big-lbl">Voted</div>
        </div>
        <div style="text-align:center">
          <div class="big-num" style="color:var(--gold)"><?= $turnout['pct'] ?>%</div>
          <div class="big-lbl">Turnout</div>
        </div>
        <div style="display:flex;flex-direction:column;gap:8px">
          <a href="<?= APP_URL ?>/admin/ai_analytics.php?election_id=<?= $elId ?>&tab=summary" class="btn btn-gold btn-sm">🤖 AI Summary</a>
          <?php if ($el['status'] === 'closed'): ?>
            <a href="<?= APP_URL ?>/admin/elections.php?action=status&id=<?= $elId ?>&status=published" class="btn btn-success btn-sm" onclick="return confirm('Publish official results?')">Publish Results</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Results Per Position -->
  <?php foreach ($posList as $pos):
    $posResults = $byPos[$pos['position_id']] ?? [];
    $maxVotes   = max(array_column($posResults, 'vote_count') ?: [1]);
    $totalVotes = array_sum(array_column($posResults, 'vote_count'));
  ?>
  <div class="card results-card" style="margin-bottom:14px">
    <div class="card-head">
      <h3><?= htmlspecialchars($pos['position_name']) ?></h3>
      <span style="font-family:'DM Mono',monospace;font-size:12px;color:var(--muted)"><?= count($posResults) ?> candidates &middot; <?= $totalVotes ?> votes</span>
    </div>
    <div class="card-body">
      <?php foreach ($posResults as $i => $r):
        $pct = $totalVotes > 0 ? round($r['vote_count'] / $totalVotes * 100, 1) : 0;
        $barW = $maxVotes > 0 ? round($r['vote_count'] / $maxVotes * 100) : 0;
      ?>
      <div class="result-row">
        <div class="result-rank <?= $i === 0 ? 'first' : '' ?>"><?= $i + 1 ?></div>
        <div style="min-width:200px">
          <div class="result-name"><?= htmlspecialchars($r['full_name']) ?></div>
          <div class="result-party"><?= htmlspecialchars($r['party_affiliation'] ?: 'Independent') ?></div>
        </div>
        <div class="result-bar-wrap">
          <div class="result-bar">
            <div class="result-bar-fill <?= $i === 0 ? 'first' : '' ?>" style="width:<?= $barW ?>%"></div>
          </div>
        </div>
        <div class="result-count"><?= $r['vote_count'] ?> votes</div>
        <div class="result-pct"><?= $pct ?>%</div>
        <?php if ($i === 0 && $totalVotes > 0): ?>
          <div style="margin-left:8px;font-size:20px" title="Winner">🏆</div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
      <?php if (empty($posResults)): ?>
        <div class="empty-state">No candidates or votes for this position.</div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>

</div>

<?php endforeach; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>