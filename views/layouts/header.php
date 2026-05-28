<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? APP_NAME) ?> — VoteSecure</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Mono:wght@300;400;500&family=Newsreader:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= APP_URL ?>/public/css/style.css">
</head>
<body>

<nav class="topbar">
  <a class="topbar-logo" href="<?= APP_URL ?>/admin/dashboard.php">
    <div class="topbar-logo-icon">🗳️</div>
    VoteSecure
  </a>

  <div class="topbar-nav">
    <a href="<?= APP_URL ?>/admin/dashboard.php"   class="<?= ($activePage ?? '') === 'dashboard'   ? 'active' : '' ?>">Dashboard</a>
    <a href="<?= APP_URL ?>/admin/elections.php"   class="<?= ($activePage ?? '') === 'elections'   ? 'active' : '' ?>">Elections</a>
    <a href="<?= APP_URL ?>/admin/candidates.php"  class="<?= ($activePage ?? '') === 'candidates'  ? 'active' : '' ?>">Candidates</a>
    <a href="<?= APP_URL ?>/admin/voters.php"      class="<?= ($activePage ?? '') === 'voters'      ? 'active' : '' ?>">Voters</a>
    <a href="<?= APP_URL ?>/admin/results.php"     class="<?= ($activePage ?? '') === 'results'     ? 'active' : '' ?>">Results</a>
    <a href="<?= APP_URL ?>/admin/ai_analytics.php" class="<?= ($activePage ?? '') === 'ai'         ? 'active ai-link' : 'ai-link' ?>">🤖 AI Analytics</a>
    <a href="<?= APP_URL ?>/admin/audit_log.php"   class="<?= ($activePage ?? '') === 'auditlog'    ? 'active' : '' ?>">Audit Log</a>
  </div>

  <div class="topbar-right">
    <div class="user-chip">
      <div class="user-avatar"><?= strtoupper(substr($_SESSION['full_name'] ?? 'A', 0, 1)) ?></div>
      <div class="user-info">
        <div class="name"><?= htmlspecialchars($_SESSION['full_name'] ?? '') ?></div>
        <div class="role-badge"><?= htmlspecialchars($_SESSION['role'] ?? '') ?></div>
      </div>
    </div>
    <?php $logoutSession = in_array($_SESSION['role'] ?? '', ['system_admin', 'election_admin'], true) ? 'admin' : 'voter'; ?>
    <a href="<?= APP_URL ?>/logout.php?session=<?= $logoutSession ?>" class="btn btn-outline btn-sm">Logout</a>
  </div>
</nav>

<div class="app-content">