<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Profile — VoteSecure</title>
  <link rel="stylesheet" href="<?= APP_URL ?>/public/css/style.css">
</head>
<body>
<?php
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    header('Location: ' . (defined('APP_URL') ? APP_URL . '/index.php' : '/index.php'));
    exit;
}
require __DIR__ . '/../layouts/header.php'; ?>

<div class="app-content">
  <div class="page-header">
    <div>
      <div class="page-title">My Profile</div>
      <div class="page-sub">Update your profile information</div>
    </div>
  </div>

  <?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
    <?php unset($_SESSION['flash_success']); ?>
  <?php endif; ?>

  <?php if (!empty($_SESSION['login_error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['login_error']) ?></div>
    <?php unset($_SESSION['login_error']); ?>
  <?php endif; ?>

  <form method="POST" action="<?= APP_URL ?>/voter/profile.php" enctype="multipart/form-data" style="max-width:640px">
    <div class="form-group">
      <label>Display name</label>
      <input name="display_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label>School email</label>
      <input name="school_email" type="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label>School ID</label>
      <input name="school_id" value="<?= htmlspecialchars($user['school_id'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label>Department</label>
      <input name="department" value="<?= htmlspecialchars($user['department'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label>Section</label>
      <input name="section" value="<?= htmlspecialchars($user['section'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label>Photo</label>
      <?php if (!empty($user['avatar'])): ?>
        <div style="margin-bottom:8px"><img src="<?= APP_URL ?>/public/uploads/avatars/<?= htmlspecialchars($user['avatar']) ?>" alt="avatar" style="height:72px;border-radius:6px"></div>
      <?php endif; ?>
      <input type="file" name="avatar" accept="image/*">
    </div>

    <h3 style="margin-top:18px">Change password</h3>
    <div class="form-group">
      <label>Current password</label>
      <input name="current_password" type="password">
    </div>
    <div class="form-group">
      <label>New password</label>
      <input name="new_password" type="password">
    </div>
    <div class="form-group">
      <label>Confirm new password</label>
      <input name="confirm_password" type="password">
    </div>

    <button class="btn btn-primary" type="submit">Save profile</button>
  </form>

</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>
