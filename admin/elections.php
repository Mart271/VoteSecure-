<<<<<<< HEAD
<?php
// admin/elections.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Services/SessionService.php';
require_once __DIR__ . '/../src/Controllers/AdminController.php';
SessionService::start(SessionService::ADMIN_SESSION);
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$ctrl   = new AdminController();
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create') {
        $res = $ctrl->createElection($_POST);
        $_SESSION[$res['success'] ? 'flash_success' : 'flash_error'] =
            $res['success'] ? 'Election created successfully.' : $res['message'];
    } elseif ($action === 'add_position') {
        $res = $ctrl->addPosition($_POST);
        $_SESSION[$res['success'] ? 'flash_success' : 'flash_error'] =
            $res['success'] ? 'Position added successfully.' : $res['message'];
    }
    header('Location: ' . APP_URL . '/admin/elections.php'); exit;
} elseif ($action === 'status' && isset($_GET['id'])) {
    $ctrl->updateElectionStatus((int)$_GET['id'], $_GET['status'] ?? 'draft');
    $_SESSION['flash_success'] = 'Election status updated.';
    header('Location: ' . APP_URL . '/admin/elections.php'); exit;
} elseif ($action === 'delete' && isset($_GET['id'])) {
    $ctrl->deleteElection((int)$_GET['id']);
    $_SESSION['flash_success'] = 'Election deleted.';
    header('Location: ' . APP_URL . '/admin/elections.php'); exit;
}

$elections = $ctrl->listElections();
=======
<?php
// admin/elections.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Services/SessionService.php';
require_once __DIR__ . '/../src/Controllers/AdminController.php';
SessionService::start(SessionService::ADMIN_SESSION);
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$ctrl   = new AdminController();
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create') {
        $res = $ctrl->createElection($_POST);
        $_SESSION[$res['success'] ? 'flash_success' : 'flash_error'] =
            $res['success'] ? 'Election created successfully.' : $res['message'];
    } elseif ($action === 'add_position') {
        $res = $ctrl->addPosition($_POST);
        $_SESSION[$res['success'] ? 'flash_success' : 'flash_error'] =
            $res['success'] ? 'Position added successfully.' : $res['message'];
    }
    header('Location: ' . APP_URL . '/admin/elections.php'); exit;
} elseif ($action === 'status' && isset($_GET['id'])) {
    $ctrl->updateElectionStatus((int)$_GET['id'], $_GET['status'] ?? 'draft');
    $_SESSION['flash_success'] = 'Election status updated.';
    header('Location: ' . APP_URL . '/admin/elections.php'); exit;
} elseif ($action === 'delete' && isset($_GET['id'])) {
    $ctrl->deleteElection((int)$_GET['id']);
    $_SESSION['flash_success'] = 'Election deleted.';
    header('Location: ' . APP_URL . '/admin/elections.php'); exit;
}

$elections = $ctrl->listElections();
>>>>>>> 4b4892c0a36933c726154fb629a76e5be16d9c40
require __DIR__ . '/../views/admin/elections.php';