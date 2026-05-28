<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ballot Confirmed — VoteSecure</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Mono:wght@300;400;500&family=Newsreader:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= APP_URL ?>/public/css/style.css">
</head>
<body class="confirmation-body">
<?php
// voter/confirmation.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Services/SessionService.php';
require_once __DIR__ . '/../src/Services/AuthService.php';
SessionService::start(SessionService::VOTER_SESSION);

$auth    = new AuthService();
$auth->requireRole('voter');

$confirm = $_SESSION['ballot_confirm'] ?? null;
if (!$confirm) {
    header('Location: ' . APP_URL . '/voter/portal.php'); exit;
}
unset($_SESSION['ballot_confirm']);
?>

<div class="confirmation-wrap">
  <div class="success-icon" style="animation: popIn 0.6s cubic-bezier(0.175,0.885,0.32,1.275)">🗳️</div>
  <h1 class="success-title">Vote Recorded!</h1>
  <p class="success-sub">Your ballot has been securely submitted and permanently recorded.</p>

  <div class="confirmation-receipt">
    <div class="receipt-row">
      <span class="receipt-key">Voter Code</span>
      <span class="receipt-val code"><?= htmlspecialchars($confirm['voter_code']) ?></span>
    </div>
    <div class="receipt-row">
      <span class="receipt-key">Ballot ID</span>
      <span class="receipt-val code">#<?= htmlspecialchars($confirm['ballot_id']) ?></span>
    </div>
    <div class="receipt-row">
      <span class="receipt-key">Submitted At</span>
      <span class="receipt-val"><?= htmlspecialchars($confirm['submitted_at']) ?></span>
    </div>
    <div class="receipt-row">
      <span class="receipt-key">Voter Name</span>
      <span class="receipt-val"><?= htmlspecialchars($_SESSION['full_name'] ?? '') ?></span>
    </div>
  </div>

  <p style="font-family:'DM Mono',monospace;font-size:12px;color:rgba(255,255,255,0.5);margin-bottom:28px">
    Keep your voter code as proof of participation.
  </p>

  <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
    <a href="<?= APP_URL ?>/voter/portal.php" class="btn btn-gold" style="padding:14px 32px;font-size:15px">Return to Portal</a>
    <button onclick="window.print()" class="btn btn-outline" style="padding:14px 24px;color:white;border-color:rgba(255,255,255,0.4)">🖨 Print Receipt</button>
  </div>
</div>

</body>
</html>