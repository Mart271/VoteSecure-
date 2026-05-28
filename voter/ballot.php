<?php
// voter/ballot.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Services/SessionService.php';
require_once __DIR__ . '/../src/Services/AuthService.php';
require_once __DIR__ . '/../src/Controllers/VoterController.php';
SessionService::start(SessionService::VOTER_SESSION);

$auth = new AuthService();
$auth->requireRole('voter');
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$ctrl       = new VoterController();
$electionId = (int) ($_GET['election_id'] ?? 0);

if (!$electionId) {
    header('Location: ' . APP_URL . '/voter/portal.php'); exit;
}

$data = $ctrl->getBallot($electionId);

if (isset($data['error'])) {
    $_SESSION['flash_success'] = $data['error'];
    header('Location: ' . APP_URL . '/voter/portal.php'); exit;
}

require __DIR__ . '/../views/voter/ballot.php';