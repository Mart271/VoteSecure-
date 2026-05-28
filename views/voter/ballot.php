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
<title>Cast Ballot — VoteSecure</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Mono:wght@300;400;500&family=Newsreader:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= APP_URL ?>/public/css/style.css">
</head>
<body class="ballot-body">

<!-- BALLOT HEADER -->
<div class="ballot-header">
  <div>
    <div class="ballot-title"><?= htmlspecialchars($data['election']['title']) ?></div>
    <div class="ballot-subtitle">Cast your ballot carefully — each vote counts once and cannot be changed.</div>
  </div>
  <div class="timer" id="ballot-timer">--:--:--</div>
</div>

<!-- VOTER CONFIRMATION -->
<div class="ballot-body-wrap">
  <div class="voter-confirm-box">
    <span style="font-size:28px">🔐</span>
    <div>
      <div class="voter-confirm-code"><?= htmlspecialchars($data['voter']['voter_code']) ?></div>
      <div class="voter-confirm-name"><?= htmlspecialchars($_SESSION['full_name'] ?? '') ?></div>
    </div>
    <div class="voter-confirm-status">Identity Verified ✓</div>
  </div>

  <!-- PROGRESS BAR -->
  <div class="progress-bar-wrap">
    <div class="progress-steps" id="progress-steps"></div>
    <div class="progress-label" id="progress-label">Step 1 of <?= count($data['positions']) ?></div>
  </div>

  <!-- BALLOT FORM -->
  <form id="ballot-form" method="POST" action="<?= APP_URL ?>/voter/submit_ballot.php">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <input type="hidden" name="election_id" value="<?= $data['election']['election_id'] ?>">

    <?php
    $avatarColors = ['#1a6fc4','#c0392b','#1e8449','#7d3c98','#d35400','#117a65'];
    foreach ($data['positions'] as $pIdx => $pos):
      $candidates = array_filter($data['candidates'], fn($c) => $c['position_id'] == $pos['position_id']);
    ?>
    <div class="ballot-position" id="position-<?= $pIdx ?>" style="<?= $pIdx > 0 ? 'display:none' : '' ?>">
      <div class="position-label"><?= htmlspecialchars($pos['position_name']) ?></div>
      <div class="position-hint">Vote for <?= $pos['max_votes'] ?> candidate<?= $pos['max_votes'] > 1 ? 's' : '' ?></div>

      <div class="candidates-grid">
        <?php foreach (array_values($candidates) as $cIdx => $c):
          $initials = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ', $c['full_name']), 0, 2))));
          $color    = $avatarColors[$cIdx % count($avatarColors)];
        ?>
        <label class="candidate-card" for="cand-<?= $pos['position_id'] ?>-<?= $c['candidate_id'] ?>">
          <input type="<?= $pos['max_votes'] > 1 ? 'checkbox' : 'radio' ?>"
                 name="choices[<?= $pos['position_id'] ?>]<?= $pos['max_votes'] > 1 ? '[]' : '' ?>"
                 id="cand-<?= $pos['position_id'] ?>-<?= $c['candidate_id'] ?>"
                 value="<?= $c['candidate_id'] ?>"
                 class="candidate-radio"
                 onchange="updateCardStyle(this)">
          <div class="candidate-avatar" style="background:<?= $color ?>"><?= $initials ?></div>
          <div class="candidate-info">
            <div class="candidate-name"><?= htmlspecialchars($c['full_name']) ?></div>
            <div class="candidate-party"><?= htmlspecialchars($c['party_affiliation'] ?: 'Independent') ?></div>
            <?php if (!empty($c['platform'])): ?>
              <div class="candidate-platform"><?= htmlspecialchars(substr($c['platform'], 0, 90)) ?>...</div>
            <?php endif; ?>
          </div>
        </label>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>

    <!-- NAVIGATION -->
    <div class="ballot-nav">
      <button type="button" class="btn btn-outline" id="btn-prev" onclick="prevStep()" style="display:none">← Back</button>
      <button type="button" class="btn btn-primary" id="btn-next" onclick="nextStep()">Next Position →</button>
      <button type="button" class="btn btn-success" id="btn-review" onclick="showReview()" style="display:none">Review Ballot →</button>
    </div>
  </form>

</div><!-- /.ballot-body-wrap -->

<!-- REVIEW MODAL -->
<div class="modal-overlay" id="modal-review">
  <div class="modal">
    <div class="modal-head">
      <h3>📋 Review Your Ballot</h3>
      <button class="modal-close" onclick="closeModal('modal-review')">×</button>
    </div>
    <div class="modal-body">
      <p style="color:var(--muted);font-size:14px;margin-bottom:16px">Please review your selections. Once submitted, your ballot cannot be changed.</p>
      <div id="review-content"></div>
    </div>
    <div class="modal-foot">
      <button class="btn btn-outline" onclick="closeModal('modal-review')">Edit Choices</button>
      <button class="btn btn-success" onclick="submitBallot()">✓ Confirm & Submit Ballot</button>
    </div>
  </div>
</div>

<?php
$positionsJson  = json_encode(array_values($data['positions']));
$candidatesJson = json_encode(array_values($data['candidates']));
$endTime        = strtotime($data['election']['end_datetime']);
?>
<script>
const positions   = <?= $positionsJson ?>;
const candidates  = <?= $candidatesJson ?>;
const endTime     = <?= $endTime ?> * 1000;
let currentStep   = 0;

// ── Timer
function updateTimer() {
  const diff = endTime - Date.now();
  if (diff <= 0) { document.getElementById('ballot-timer').textContent = 'CLOSED'; return; }
  const h = String(Math.floor(diff / 3600000)).padStart(2,'0');
  const m = String(Math.floor(diff % 3600000 / 60000)).padStart(2,'0');
  const s = String(Math.floor(diff % 60000 / 1000)).padStart(2,'0');
  document.getElementById('ballot-timer').textContent = `${h}:${m}:${s}`;
}
setInterval(updateTimer, 1000);
updateTimer();

// ── Progress
function renderProgress() {
  document.getElementById('progress-steps').innerHTML =
    positions.map((_, i) => `<div class="progress-step ${i < currentStep ? 'done' : i === currentStep ? 'current' : ''}"></div>`).join('');
  document.getElementById('progress-label').textContent = `Step ${currentStep + 1} of ${positions.length}: ${positions[currentStep]?.position_name}`;
}

function nextStep() {
  if (currentStep < positions.length - 1) {
    document.getElementById(`position-${currentStep}`).style.display = 'none';
    currentStep++;
    document.getElementById(`position-${currentStep}`).style.display = 'block';
    updateNav();
  }
  renderProgress();
}

function prevStep() {
  if (currentStep > 0) {
    document.getElementById(`position-${currentStep}`).style.display = 'none';
    currentStep--;
    document.getElementById(`position-${currentStep}`).style.display = 'block';
    updateNav();
  }
  renderProgress();
}

function updateNav() {
  document.getElementById('btn-prev').style.display   = currentStep > 0 ? '' : 'none';
  document.getElementById('btn-next').style.display   = currentStep < positions.length - 1 ? '' : 'none';
  document.getElementById('btn-review').style.display = currentStep === positions.length - 1 ? '' : 'none';
}

function updateCardStyle(input) {
  const name = input.name;
  if (input.type === 'radio') {
    document.querySelectorAll(`input[name="${name}"]`).forEach(r => r.closest('.candidate-card').classList.remove('selected'));
  }
  input.closest('.candidate-card').classList.toggle('selected', input.checked);
}

function showReview() {
  const pos = positions;
  let html   = '';
  pos.forEach(p => {
    const inputs   = document.querySelectorAll(`input[name="choices[${p.position_id}]"], input[name="choices[${p.position_id}][]"]`);
    const selected = [...inputs].filter(i => i.checked).map(i => {
      const c = candidates.find(c => c.candidate_id == i.value);
      return c ? `<strong>${c.full_name}</strong> <em>(${c.party_affiliation || 'Independent'})</em>` : '—';
    });
    html += `<div class="review-row">
      <div class="review-position">${p.position_name}</div>
      <div class="review-selection">${selected.length ? selected.join(', ') : '<span style="color:var(--red)">⚠ No selection</span>'}</div>
    </div>`;
  });
  document.getElementById('review-content').innerHTML = html;
  openModal('modal-review');
}

function submitBallot() {
  document.getElementById('ballot-form').submit();
}

// Init
renderProgress();
updateNav();
</script>

<script src="<?= APP_URL ?>/public/js/app.js"></script>
</body>
</html>