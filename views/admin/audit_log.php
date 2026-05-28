<?php
// views/admin/audit_log.php
$pageTitle  = 'Audit Log';
$activePage = 'auditlog';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
  <div>
    <div class="page-title">Audit Log</div>
    <div class="page-sub">Immutable record of all system events</div>
  </div>
  <span style="font-family:'DM Mono',monospace;font-size:13px;color:var(--muted)"><?= count($logs) ?> entries</span>
</div>

<div class="filter-bar" style="margin-bottom:20px">
  <input type="text" id="log-search" placeholder="🔍  Search logs..." oninput="filterLogs()">
  <select id="log-action-filter" onchange="filterLogs()">
    <option value="">All Actions</option>
    <option>LOGIN_SUCCESS</option>
    <option>LOGIN_FAILED</option>
    <option>BALLOT_CAST</option>
    <option>ELECTION_CREATED</option>
    <option>ELECTION_STATUS_CHANGED</option>
    <option>CANDIDATE_ADDED</option>
    <option>VOTER_REGISTERED</option>
    <option>AI_ANALYSIS</option>
    <option>AI_FRAUD_DETECTION</option>
  </select>
</div>

<div class="card">
  <div id="log-list">
    <?php $audit = new AuditLog(); foreach ($logs as $log): ?>
      <div class="log-entry" data-action="<?= htmlspecialchars($log['action']) ?>">
        <span class="log-icon"><?= $audit->getIconForAction($log['action']) ?></span>
        <div style="flex:1">
          <div style="display:flex;align-items:center;gap:10px">
            <span class="log-action"><?= htmlspecialchars($log['action']) ?></span>
            <span style="font-family:'DM Mono',monospace;font-size:11px;color:var(--muted)">#{$log['log_id']}</span>
          </div>
          <div class="log-desc"><?= htmlspecialchars($log['description']) ?></div>
          <div class="log-meta">
            <?= htmlspecialchars($log['full_name'] ?? 'System') ?>
            (<?= htmlspecialchars($log['username'] ?? '—') ?>)
            &nbsp;&middot;&nbsp; <?= htmlspecialchars($log['ip_address'] ?? '—') ?>
            &nbsp;&middot;&nbsp; <?= htmlspecialchars($log['created_at']) ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (empty($logs)): ?>
      <div class="empty-state">No audit log entries found.</div>
    <?php endif; ?>
  </div>
</div>

<?php
$extraJs = <<<'JS'
function filterLogs() {
  const search = document.getElementById('log-search').value.toLowerCase();
  const action = document.getElementById('log-action-filter').value;
  document.querySelectorAll('#log-list .log-entry').forEach(entry => {
    const text   = entry.textContent.toLowerCase();
    const act    = entry.dataset.action;
    const matchS = !search || text.includes(search);
    const matchA = !action || act === action;
    entry.style.display = (matchS && matchA) ? '' : 'none';
  });
}
JS;
require __DIR__ . '/../layouts/footer.php';
?>