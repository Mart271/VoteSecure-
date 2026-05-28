<?php
// views/admin/elections.php
$pageTitle  = 'Elections';
$activePage = 'elections';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
  <div>
    <div class="page-title">Elections</div>
    <div class="page-sub">Manage all election events</div>
  </div>
  <button class="btn btn-primary" onclick="openModal('modal-create-election')">+ New Election</button>
</div>

<!-- FLASH MESSAGES -->
<?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- ELECTIONS GRID -->
<div class="three-col">
  <?php foreach ($elections as $el): ?>
    <div class="election-card">
      <div class="election-card-head">
        <div class="ec-title"><?= htmlspecialchars($el['title']) ?></div>
        <div style="margin:6px 0"><span class="badge badge-<?= $el['status'] ?>"><?= $el['status'] ?></span></div>
        <div class="ec-dates">📅 <?= substr($el['start_datetime'], 0, 10) ?> &rarr; <?= substr($el['end_datetime'], 0, 10) ?></div>
      </div>
      <div class="election-card-body">
        <p class="ec-desc"><?= htmlspecialchars(substr($el['description'] ?? '', 0, 100)) ?>...</p>
        <div class="ec-meta">
          <div class="ec-meta-item"><div class="num"><?= $el['position_count'] ?></div><div class="lbl">Positions</div></div>
          <div class="ec-meta-item"><div class="num"><?= $el['candidate_count'] ?></div><div class="lbl">Candidates</div></div>
          <div class="ec-meta-item"><div class="num"><?= $el['voter_count'] ?></div><div class="lbl">Voters</div></div>
          <div class="ec-meta-item"><div class="num"><?= $el['voted_count'] ?? 0 ?></div><div class="lbl">Voted</div></div>
        </div>
        <div class="ec-actions">
          <?php if ($el['status'] === 'draft'): ?>
            <a href="?action=status&id=<?= $el['election_id'] ?>&status=active" class="btn btn-gold btn-sm" onclick="return confirm('Activate this election?')">Activate</a>
          <?php elseif ($el['status'] === 'active'): ?>
            <a href="?action=status&id=<?= $el['election_id'] ?>&status=closed" class="btn btn-danger btn-sm" onclick="return confirm('Close this election?')">Close</a>
            <a href="<?= APP_URL ?>/admin/ai_analytics.php?election_id=<?= $el['election_id'] ?>" class="btn btn-outline btn-sm">🤖 AI</a>
          <?php elseif ($el['status'] === 'closed'): ?>
            <a href="?action=status&id=<?= $el['election_id'] ?>&status=published" class="btn btn-success btn-sm" onclick="return confirm('Publish results?')">Publish Results</a>
            <a href="<?= APP_URL ?>/admin/ai_analytics.php?election_id=<?= $el['election_id'] ?>" class="btn btn-outline btn-sm">🤖 AI</a>
          <?php elseif ($el['status'] === 'published'): ?>
            <a href="<?= APP_URL ?>/admin/results.php?election_id=<?= $el['election_id'] ?>" class="btn btn-outline btn-sm">View Results</a>
            <a href="<?= APP_URL ?>/admin/ai_analytics.php?election_id=<?= $el['election_id'] ?>" class="btn btn-outline btn-sm">🤖 AI</a>
          <?php endif; ?>
          <a href="<?= APP_URL ?>/admin/candidates.php?election_id=<?= $el['election_id'] ?>" class="btn btn-outline btn-sm">Manage Candidates</a>
          <a href="<?= APP_URL ?>/admin/voters.php?election_id=<?= $el['election_id'] ?>" class="btn btn-outline btn-sm">Register Voter</a>
          <button class="btn btn-outline btn-sm" onclick="openAddPositionModal(<?= $el['election_id'] ?>, '<?= htmlspecialchars(addslashes($el['title'])) ?>')">+ Position</button>
          <a href="?action=delete&id=<?= $el['election_id'] ?>" class="btn btn-sm" style="color:var(--red)" onclick="return confirm('Delete this election and all related data?')">Delete</a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <?php if (empty($elections)): ?>
    <div class="empty-state" style="grid-column:1/-1">No elections found. Create your first election!</div>
  <?php endif; ?>
</div>

<!-- CREATE ELECTION MODAL -->
<div class="modal-overlay" id="modal-create-election">
  <div class="modal">
    <div class="modal-head">
      <h3>🗳️ Create New Election</h3>
      <button class="modal-close" onclick="closeModal('modal-create-election')">×</button>
    </div>
    <form method="POST" action="?action=create">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
      <div class="modal-body">
        <div class="form-group">
          <label>Election Title *</label>
          <input type="text" name="title" placeholder="e.g., Student Council Election 2025" required>
        </div>
        <div class="form-group">
          <label>Description</label>
          <textarea name="description" rows="3" placeholder="Brief overview of this election..."></textarea>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Start Date & Time *</label>
            <input type="datetime-local" name="start_datetime" required>
          </div>
          <div class="form-group">
            <label>End Date & Time *</label>
            <input type="datetime-local" name="end_datetime" required>
          </div>
        </div>
        <div class="form-group">
          <label>Initial Status</label>
          <select name="status">
            <option value="draft">Draft (configure before activating)</option>
            <option value="active">Active (open for voting immediately)</option>
          </select>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" onclick="closeModal('modal-create-election')">Cancel</button>
        <button type="submit" class="btn btn-primary">Create Election</button>
      </div>
    </form>
  </div>
</div>

<!-- ADD POSITION MODAL -->
<div class="modal-overlay" id="modal-add-position">
  <div class="modal">
    <div class="modal-head">
      <h3>📌 Add Position</h3>
      <button class="modal-close" onclick="closeModal('modal-add-position')">×</button>
    </div>
    <form method="POST" action="<?= APP_URL ?>/admin/elections.php?action=add_position">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
      <input type="hidden" name="election_id" id="pos-election-id">
      <div class="modal-body">
        <p class="form-note">Adding position to: <strong id="pos-election-title"></strong></p>
        <div class="form-group">
          <label>Position Name *</label>
          <input type="text" name="position_name" placeholder="e.g., President, Vice-President" required>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Max Votes Per Voter</label>
            <input type="number" name="max_votes" value="1" min="1" max="10">
          </div>
          <div class="form-group">
            <label>Display Order</label>
            <input type="number" name="display_order" value="1" min="1">
          </div>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" onclick="closeModal('modal-add-position')">Cancel</button>
        <button type="submit" class="btn btn-primary">Add Position</button>
      </div>
    </form>
  </div>
</div>

<?php
$extraJs = <<<JS
function openAddPositionModal(id, title) {
  document.getElementById('pos-election-id').value    = id;
  document.getElementById('pos-election-title').textContent = title;
  openModal('modal-add-position');
}
JS;
require __DIR__ . '/../layouts/footer.php';
?>