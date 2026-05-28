<?php
// admin/voters.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Services/SessionService.php';
require_once __DIR__ . '/../src/Controllers/AdminController.php';
require_once __DIR__ . '/../src/Models/Election.php';
SessionService::start(SessionService::ADMIN_SESSION);
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$ctrl   = new AdminController();
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
    $res = $ctrl->registerVoter($_POST);
    $_SESSION[$res['success'] ? 'flash_success' : 'flash_error'] =
        $res['success'] ? "Voter registered. Code: {$res['voter_code']}" : $res['message'];
    header('Location: ' . APP_URL . '/admin/voters.php'); exit;
}

$voters    = $ctrl->listVoters();
$elections = (new Election())->getAllWithStats();
require __DIR__ . '/../views/admin/voters.php';