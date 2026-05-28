<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Create Account — VoteSecure</title>
  <link rel="stylesheet" href="<?= APP_URL ?>/public/css/style.css">
</head>
<body class="login-body">

<div class="login-wrap">
  <div class="login-right">
    <div class="login-form-wrap">
      <h2>Create your voter account</h2>
      <p class="sub">Use your school email to register</p>

      <?php if (!empty($_SESSION['login_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['login_error']) ?></div>
        <?php unset($_SESSION['login_error']); ?>
      <?php endif; ?>

      <form method="POST" action="<?= APP_URL ?>/index.php?action=register" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

        <div class="form-group">
          <label for="display_name">Display name</label>
          <input id="display_name" name="display_name" placeholder="Your full name" required>
        </div>

        <div class="form-group">
          <label for="school_email">School email</label>
          <input id="school_email" name="school_email" type="email" placeholder="you@school.edu" required>
        </div>

        <div class="form-group">
          <label for="department">Department</label>
          <input id="department" name="department" placeholder="e.g. Computer Science">
        </div>

        <div class="form-group">
          <label for="section">Section</label>
          <input id="section" name="section" placeholder="e.g. 1A">
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" required>
        </div>

        <div class="form-group">
          <label for="confirm_password">Confirm password</label>
          <input id="confirm_password" name="confirm_password" type="password" required>
        </div>

        <button class="btn btn-primary" type="submit">Create account</button>
      </form>

      <p style="margin-top:12px">Already registered? <a href="<?= APP_URL ?>/index.php">Sign in</a></p>
    </div>
  </div>
</div>

</body>
</html>
