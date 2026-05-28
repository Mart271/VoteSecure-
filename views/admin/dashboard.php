<?php
// views/admin/dashboard.php
$pageTitle  = 'Dashboard';
$activePage = 'dashboard';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
  <div>
    <div class="page-title">Dashboard</div>
    <div class="page-sub">Welcome back, <?= htmlspecialchars($_SESSION['full_name'] ?? '') ?></div>
  </div>
  <a href="<?= APP_URL ?>/admin/elections.php?action=create" class="btn btn-primary">+ New Election</a>
</div>

<!-- STAT CARDS -->
<div class="stats-grid">
  <div class="stat-card blue">
    <span class="icon">🗳️</span>
    <div class="value"><?= $data['stats']['total_elections'] ?></div>
    <div class="label">Total Elections</div>
  </div>
  <div class="stat-card gold">
    <span class="icon">✅</span>
    <div class="value"><?= $data['stats']['active_elections'] ?></div>
    <div class="label">Active Elections</div>
  </div>
  <div class="stat-card green">
    <span class="icon">👥</span>
    <div class="value"><?= $data['total_voters'] ?></div>
    <div class="label">Registered Voters</div>
  </div>
  <div class="stat-card red">
    <span class="icon">📊</span>
    <div class="value"><?= $data['avg_turnout'] ?>%</div>
    <div class="label">Avg. Voter Turnout</div>
  </div>
</div>

<!-- MAIN GRID -->
<div class="two-col">

  <!-- Elections Table -->
  <div class="card">
    <div class="card-head">
      <h3>Recent Elections</h3>
      <a href="<?= APP_URL ?>/admin/elections.php" class="btn btn-outline btn-sm">View All</a>
    </div>
    <table class="data-table">
      <thead>
        <tr><th>Title</th><th>Status</th><th>Voters</th><th>Voted</th></tr>
      </thead>
      <tbody>
        <?php foreach (array_slice($data['elections'], 0, 6) as $el): ?>
          <tr>
            <td><a href="<?= APP_URL ?>/admin/elections.php?id=<?= $el['election_id'] ?>" class="table-link"><?= htmlspecialchars($el['title']) ?></a></td>
            <td><span class="badge badge-<?= $el['status'] ?>"><?= $el['status'] ?></span></td>
            <td><?= $el['voter_count'] ?></td>
            <td><?= $el['voted_count'] ?> / <?= $el['voter_count'] ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($data['elections'])): ?>
          <tr><td colspan="4"><div class="empty-state">No elections yet.</div></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Activity Feed -->
  <div class="card">
    <div class="card-head">
      <h3>Recent Activity</h3>
      <a href="<?= APP_URL ?>/admin/audit_log.php" class="btn btn-outline btn-sm">View All</a>
    </div>
    <div class="log-list">
      <?php foreach ($data['recent_logs'] as $log): ?>
        <div class="log-entry">
          <span class="log-icon"><?= (new AuditLog())->getIconForAction($log['action']) ?></span>
          <div>
            <div class="log-action"><?= htmlspecialchars($log['action']) ?></div>
            <div class="log-desc"><?= htmlspecialchars($log['description']) ?></div>
            <div class="log-meta"><?= htmlspecialchars($log['full_name'] ?? 'System') ?> &middot; <?= $log['created_at'] ?></div>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($data['recent_logs'])): ?>
        <div class="empty-state">No activity yet.</div>
      <?php endif; ?>
    </div>
  </div>

</div>

<!-- AI QUICK ACTIONS -->
<div class="card" style="margin-top:20px">
  <div class="card-head">
    <h3>🤖 AI Analytics Quick Actions</h3>
    <a href="<?= APP_URL ?>/admin/ai_analytics.php" class="btn btn-gold btn-sm">Open AI Dashboard</a>
  </div>
  <div class="card-body" style="display:flex;gap:16px;flex-wrap:wrap">
    <div class="ai-quick-card">
      <div class="ai-quick-icon">📊</div>
      <div class="ai-quick-title">Election Analysis</div>
      <div class="ai-quick-desc">Deep analytics on voting patterns and candidate performance</div>
    </div>
    <div class="ai-quick-card">
      <div class="ai-quick-icon">🔍</div>
      <div class="ai-quick-title">Fraud Detection</div>
      <div class="ai-quick-desc">Detect anomalies, suspicious IP clusters, and irregular patterns</div>
    </div>
    <div class="ai-quick-card">
      <div class="ai-quick-icon">📈</div>
      <div class="ai-quick-title">Turnout Prediction</div>
      <div class="ai-quick-desc">Predict voter participation before elections close</div>
    </div>
    <div class="ai-quick-card">
      <div class="ai-quick-icon">📝</div>
      <div class="ai-quick-title">Results Summary</div>
      <div class="ai-quick-desc">Auto-generate official results narrative with AI</div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>