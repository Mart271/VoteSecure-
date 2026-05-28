<?php
// voter/submit_ballot.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Services/SessionService.php';
require_once __DIR__ . '/../src/Services/AuthService.php';
require_once __DIR__ . '/../src/Controllers/VoterController.php';
SessionService::start(SessionService::VOTER_SESSION);

$auth = new AuthService();
$auth->requireRole('voter');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_URL . '/voter/portal.php'); exit;
}

// CSRF check
if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    $_SESSION['flash_error'] = 'Invalid request. Please try again.';
    header('Location: ' . APP_URL . '/voter/portal.php'); exit;
}

$ctrl       = new VoterController();
$electionId = (int) ($_POST['election_id'] ?? 0);
$choices    = $_POST['choices'] ?? [];

$result = $ctrl->castBallot($electionId, $choices);

if ($result['success']) {
    // Store confirmation data in session for success page
    $_SESSION['ballot_confirm'] = [
        'voter_code'  => $result['voter_code'],
        'ballot_id'   => $result['ballot_id'],
        'submitted_at'=> date('Y-m-d H:i:s'),
    ];
    header('Location: ' . APP_URL . '/voter/confirmation.php'); exit;
} else {
    $_SESSION['flash_error'] = $result['message'];
    header('Location: ' . APP_URL . '/voter/portal.php'); exit;
}