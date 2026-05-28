<?php
// admin/candidates.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Services/SessionService.php';
require_once __DIR__ . '/../src/Controllers/AdminController.php';
require_once __DIR__ . '/../src/Models/Position.php';
require_once __DIR__ . '/../src/Models/Election.php';
SessionService::start(SessionService::ADMIN_SESSION);
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$ctrl   = new AdminController();
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
    $res = $ctrl->addCandidate($_POST);
    $_SESSION[$res['success'] ? 'flash_success' : 'flash_error'] =
        $res['success'] ? 'Candidate registered successfully.' : $res['message'];
    header('Location: ' . APP_URL . '/admin/candidates.php'); exit;
} elseif ($action === 'disqualify' && isset($_GET['id'])) {
    $ctrl->disqualifyCandidate((int)$_GET['id']);
    $_SESSION['flash_success'] = 'Candidate disqualified.';
    header('Location: ' . APP_URL . '/admin/candidates.php'); exit;
}

$candidates = $ctrl->listCandidates();
$elections  = (new Election())->getAllWithStats();
$positions  = (new Position())->findAll('', [], 'election_id, display_order');
require __DIR__ . '/../views/admin/candidates.php';