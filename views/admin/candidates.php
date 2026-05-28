<?php
// views/admin/candidates.php
$pageTitle  = 'Candidates';
$activePage = 'candidates';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
  <div>
    <div class="page-title">Candidates</div>
    <div class="page-sub">Register and manage election candidates</div>
  </div>
  <button class="btn btn-primary" onclick="openModal('modal-add-candidate')">+ Add Candidate</button>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<div class="card">
  <table class="data-table">
    <thead>
      <tr>
        <th>Candidate</th>
        <th>Position</th>
        <th>Election</th>
        <th>Party</th>
        <th>Platform</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $avatarColors = ['#1a6fc4','#c0392b','#1e8449','#7d3c98','#d35400','#117a65'];
      foreach ($candidates as $i => $c):
        $initials = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $c['full_name']), 0, 2))));
        $color    = $avatarColors[$i % count($avatarColors)];
      ?>
      <tr>
        <td>
          <div style="display:flex;align-items:center;gap:12px">
            <div class="candidate-avatar-sm" style="background:<?= $color ?>"><?= $initials ?></div>
            <div>
              <div style="font-weight:700"><?= htmlspecialchars($c['full_name']) ?></div>
            </div>
          </div>
        </td>
        <td><span class="chip"><?= htmlspecialchars($c['position_name']) ?></span></td>
        <td style="font-size:13px;max-width:180px"><?= htmlspecialchars($c['election_title']) ?></td>
        <td style="font-size:13px;font-style:italic;color:var(--muted)"><?= htmlspecialchars($c['party_affiliation'] ?: 'Independent') ?></td>
        <td style="font-size:12px;color:var(--muted);max-width:220px"><?= htmlspecialchars(substr($c['platform'] ?? '', 0, 80)) ?>...</td>
        <td>
          <?php if ($c['is_disqualified']): ?>
            <span class="badge" style="background:#fce4ec;color:#c62828">Disqualified</span>
          <?php else: ?>
            <span class="badge badge-active">Active</span>
          <?php endif; ?>
        </td>
        <td>
          <?php if (!$c['is_disqualified']): ?>
            <a href="?action=disqualify&id=<?= $c['candidate_id'] ?>" class="btn btn-danger btn-sm"
               onclick="return confirm('Disqualify this candidate?')">Disqualify</a>
          <?php endif; ?>
          <a href="?action=delete&id=<?= $c['candidate_id'] ?>" class="btn btn-sm" style="color:var(--muted)"
             onclick="return confirm('Remove this candidate?')">Remove</a>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($candidates)): ?>
        <tr><td colspan="7"><div class="empty-state">No candidates registered yet.</div></td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- ADD CANDIDATE MODAL -->
<div class="modal-overlay" id="modal-add-candidate">
  <div class="modal">
    <div class="modal-head">
      <h3>👤 Register Candidate</h3>
      <button class="modal-close" onclick="closeModal('modal-add-candidate')">×</button>
    </div>
    <form method="POST" action="?action=create">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
      <div class="modal-body">
        <div class="form-group">
          <label>Election *</label>
          <select name="election_id" id="cand-election-sel" onchange="loadPositions()" required>
            <option value="">— Select Election —</option>
            <?php foreach ($elections as $el): ?>
              <option value="<?= $el['election_id'] ?>"><?= htmlspecialchars($el['title']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Position *</label>
          <select name="position_id" id="cand-position-sel" required>
            <option value="">— Select Election First —</option>
          </select>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="full_name" placeholder="Candidate's full name" required>
          </div>
          <div class="form-group">
            <label>Party Affiliation</label>
            <input type="text" name="party_affiliation" placeholder="Party or Independent">
          </div>
        </div>
        <div class="form-group">
          <label>Platform Statement</label>
          <textarea name="platform" rows="3" placeholder="Candidate's campaign platform..."></textarea>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" onclick="closeModal('modal-add-candidate')">Cancel</button>
        <button type="submit" class="btn btn-primary">Register Candidate</button>
      </div>
    </form>
  </div>
</div>

<?php
// Pass positions JSON for JS
$positionsJson = json_encode($positions ?? []);
$extraJs = <<<JS
const allPositions = {$positionsJson};
function loadPositions() {
  const elId = parseInt(document.getElementById('cand-election-sel').value);
  const sel  = document.getElementById('cand-position-sel');
  const filtered = allPositions.filter(p => p.election_id === elId);
  sel.innerHTML = filtered.length
    ? filtered.map(p => `<option value="\${p.position_id}">\${p.position_name}</option>`).join('')
    : '<option value="">No positions for this election</option>';
}

window.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(window.location.search);
  const electionId = parseInt(params.get('election_id') || '0');
  if (electionId) {
    const select = document.getElementById('cand-election-sel');
    if (select) {
      select.value = electionId;
      loadPositions();
    }
  }
});
JS;
require __DIR__ . '/../layouts/footer.php';
?>