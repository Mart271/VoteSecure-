<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>VoteSecure — Login</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Mono:wght@300;400;500&family=Newsreader:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= APP_URL ?>/public/css/style.css">
</head>
<body class="login-body">

<div class="login-wrap">

  <!-- LEFT PANEL -->
  <div class="login-left">
    <div class="login-badge">
      <div class="login-badge-icon">🗳️</div>
      <span class="login-badge-text">VoteSecure</span>
    </div>
    <div class="login-left-content">
      <h1>Secure.<br><span>Transparent.</span><br>Democratic.</h1>
      <p>A trusted digital election platform for organizations and academic institutions — built for integrity, transparency, and ease of use.</p>
      <div class="login-stats">
        <div class="login-stat"><div class="num">🗳️</div><div class="lbl">Election Management</div></div>
        <div class="login-stat"><div class="num">🤖</div><div class="lbl">AI Analytics</div></div>
        <div class="login-stat"><div class="num">🔐</div><div class="lbl">Fraud Detection</div></div>
      </div>
    </div>
  </div>

  <!-- RIGHT PANEL -->
  <div class="login-right">
    <div class="login-form-wrap">
      <h2>Welcome back</h2>
      <p class="sub">Select your role and sign in</p>

      <!-- ROLE TABS — determines which form is shown -->
      <div class="role-tabs">
        <button type="button" class="role-tab active" id="tab-admin" onclick="switchRole('admin')">
          ⚙️ Administrator
        </button>
        <button type="button" class="role-tab" id="tab-voter" onclick="switchRole('voter')">
          🗳️ Voter
        </button>
      </div>

      <!-- ERROR / SUCCESS ALERTS -->
      <?php if (!empty($_SESSION['login_error'])): ?>
        <div class="alert alert-danger" id="login-alert">
          <?= htmlspecialchars($_SESSION['login_error']) ?>
        </div>
        <?php unset($_SESSION['login_error']); ?>
      <?php endif; ?>

      <?php if (!empty($_SESSION['login_success'])): ?>
        <div class="alert alert-success">
          <?= htmlspecialchars($_SESSION['login_success']) ?>
        </div>
        <?php unset($_SESSION['login_success']); ?>
      <?php endif; ?>

      <!-- ── ADMIN FORM ── -->
      <form id="form-admin" method="POST" action="<?= APP_URL ?>/index.php?action=login" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <input type="hidden" name="login_as"   value="admin">

        <div class="form-group">
          <label for="admin-username">Username</label>
          <input type="text" id="admin-username" name="username"
                 value="<?= ($lastRole ?? '') === 'admin' ? htmlspecialchars($_POST['username'] ?? '') : '' ?>"
                 placeholder="Admin username" autocomplete="username" required>
        </div>
        <div class="form-group">
          <label for="admin-password">Password</label>
          <div class="input-icon-wrap">
            <input type="password" id="admin-password" name="password"
                   placeholder="Admin password" autocomplete="current-password" required>
            <button type="button" class="toggle-pw" onclick="togglePw('admin-password')">👁</button>
          </div>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;margin-top:8px">
          Sign In as Admin &rarr;
        </button>
      </form>

      <!-- ── VOTER FORM ── -->
      <form id="form-voter" method="POST" action="<?= APP_URL ?>/index.php?action=login" novalidate
            style="display:none">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <input type="hidden" name="login_as"   value="voter">

        <div class="form-group">
          <label for="voter-username">Username</label>
          <input type="text" id="voter-username" name="username"
                 value="<?= ($lastRole ?? '') === 'voter' ? htmlspecialchars($_POST['username'] ?? '') : '' ?>"
                 placeholder="e.g. voter1" autocomplete="username" required>
        </div>
        <div class="form-group">
          <label for="voter-password">Password</label>
          <div class="input-icon-wrap">
            <input type="password" id="voter-password" name="password"
                   placeholder="Your password" autocomplete="current-password" required>
            <button type="button" class="toggle-pw" onclick="togglePw('voter-password')">👁</button>
          </div>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;margin-top:8px;background:var(--green)">
          Sign In as Voter &rarr;
        </button>
      </form>

      <!-- DEMO CREDENTIALS HINT -->
      <div class="login-hint">
        <div id="hint-admin">
          <strong>Admin accounts:</strong><br>
          admin / admin123 &nbsp;&nbsp; sysadmin / admin123
        </div>
        <div id="hint-voter" style="display:none">
          <strong>Voter accounts:</strong><br>
          voter1 / admin123 &nbsp; through &nbsp; voter20 / admin123
        </div>
      </div>

      <p style="margin-top:12px">New here? <a href="<?= APP_URL ?>/index.php?action=register">Create an account</a></p>

    </div>
  </div>

</div>

<script>
  // Restore the tab that had the error (so user sees the right form after failed login)
  const lastRole = '<?= htmlspecialchars($_SESSION['last_login_role'] ?? 'admin') ?>';
  <?php unset($_SESSION['last_login_role']); ?>
  if (lastRole === 'voter') switchRole('voter');

  function switchRole(role) {
    const isAdmin = role === 'admin';
    document.getElementById('form-admin').style.display  = isAdmin ? '' : 'none';
    document.getElementById('form-voter').style.display  = isAdmin ? 'none' : '';
    document.getElementById('tab-admin').classList.toggle('active',  isAdmin);
    document.getElementById('tab-voter').classList.toggle('active', !isAdmin);
    document.getElementById('hint-admin').style.display  = isAdmin ? '' : 'none';
    document.getElementById('hint-voter').style.display  = isAdmin ? 'none' : '';
  }

  function togglePw(id) {
    const f = document.getElementById(id);
    f.type = f.type === 'password' ? 'text' : 'password';
  }
</script>
</body>
</html>