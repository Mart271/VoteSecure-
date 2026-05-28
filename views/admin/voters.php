<?php
// views/admin/voters.php
$pageTitle  = 'Voters';
$activePage = 'voters';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
  <div>
    <div class="page-title">Voters</div>
    <div class="page-sub">Registered voter management</div>
  </div>
  <button class="btn btn-primary" onclick="openModal('modal-add-voter')">+ Register Voter</button>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- FILTER BAR -->
<div class="filter-bar">
  <input type="text" id="voter-search" placeholder="🔍  Search by name, code, or username..." oninput="filterTable()">
  <select id="voter-filter-election" onchange="filterTable()">
    <option value="">All Elections</option>
    <?php foreach ($elections as $el): ?>
      <option value="<?= $el['election_id'] ?>"><?= htmlspecialchars($el['title']) ?></option>
    <?php endforeach; ?>
  </select>
  <select id="voter-filter-status" onchange="filterTable()">
    <option value="">All Statuses</option>
    <option value="1">Voted</option>
    <option value="0">Not Voted</option>
  </select>
</div>

<div class="card">
  <table class="data-table" id="voters-table">
    <thead>
      <tr>
        <th>Voter Code</th>
        <th>Full Name</th>
        <th>Username</th>
        <th>Election</th>
        <th>Status</th>
        <th>Voted At</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($voters as $v): ?>
        <tr data-election="<?= $v['election_id'] ?>" data-voted="<?= $v['has_voted'] ?>">
          <td><code class="voter-code"><?= htmlspecialchars($v['voter_code']) ?></code></td>
          <td style="font-weight:600"><?= htmlspecialchars($v['full_name']) ?></td>
          <td style="font-family:'DM Mono',monospace;font-size:12px"><?= htmlspecialchars($v['username']) ?></td>
          <td style="font-size:13px"><?= htmlspecialchars($v['election_title'] ?? '—') ?></td>
          <td>
            <?php if ($v['has_voted']): ?>
              <span class="badge badge-active">✓ Voted</span>
            <?php else: ?>
              <span class="badge badge-draft">Pending</span>
            <?php endif; ?>
          </td>
          <td style="font-family:'DM Mono',monospace;font-size:12px;color:var(--muted)">
            <?= $v['voted_at'] ?? '—' ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($voters)): ?>
        <tr><td colspan="6"><div class="empty-state">No voters registered yet.</div></td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- REGISTER VOTER MODAL -->
<div class="modal-overlay" id="modal-add-voter">
  <div class="modal">
    <div class="modal-head">
      <h3>👥 Register Voter</h3>
      <button class="modal-close" onclick="closeModal('modal-add-voter')">×</button>
    </div>
    <form method="POST" action="?action=create">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
      <div class="modal-body">
        <div class="form-group">
          <label>Election *</label>
          <select name="election_id" id="register-election-sel" required>
            <option value="">— Select Election —</option>
            <?php foreach ($elections as $el): ?>
              <option value="<?= $el['election_id'] ?>"><?= htmlspecialchars($el['title']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="full_name" placeholder="Voter's full name" required>
          </div>
          <div class="form-group">
            <label>Username *</label>
            <input type="text" name="username" placeholder="Login username" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" placeholder="voter@email.com">
          </div>
          <div class="form-group">
            <label>Initial Password</label>
            <input type="text" name="password" placeholder="Leave blank = VoteSecure@2025">
          </div>
        </div>
        <div class="form-note">💡 A unique voter code will be auto-generated upon registration.</div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" onclick="closeModal('modal-add-voter')">Cancel</button>
        <button type="submit" class="btn btn-primary">Register Voter</button>
      </div>
    </form>
  </div>
</div>

<?php
$extraJs = <<<'JS'
function filterTable() {
  const search   = document.getElementById('voter-search').value.toLowerCase();
  const elFilter = document.getElementById('voter-filter-election').value;
  const stFilter = document.getElementById('voter-filter-status').value;
  document.querySelectorAll('#voters-table tbody tr').forEach(row => {
    const text   = row.textContent.toLowerCase();
    const el     = row.dataset.election;
    const voted  = row.dataset.voted;
    const matchS = !search   || text.includes(search);
    const matchE = !elFilter || el === elFilter;
    const matchV = !stFilter || voted === stFilter;
    row.style.display = (matchS && matchE && matchV) ? '' : 'none';
  });
}

window.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(window.location.search);
  const electionId = params.get('election_id');
  if (electionId) {
    const filter = document.getElementById('voter-filter-election');
    const registerSelect = document.getElementById('register-election-sel');
    if (filter) {
      filter.value = electionId;
    }
    if (registerSelect) {
      registerSelect.value = electionId;
    }
    filterTable();
    openModal('modal-add-voter');
  }
});
JS;
require __DIR__ . '/../layouts/footer.php';
?>