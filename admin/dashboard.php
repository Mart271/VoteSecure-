<<<<<<< HEAD
<?php
// admin/dashboard.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Services/SessionService.php';
require_once __DIR__ . '/../src/Controllers/AdminController.php';
require_once __DIR__ . '/../src/Models/AuditLog.php';
SessionService::start(SessionService::ADMIN_SESSION);
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$ctrl = new AdminController();
$data = $ctrl->dashboard();
=======
<?php
// admin/dashboard.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Services/SessionService.php';
require_once __DIR__ . '/../src/Controllers/AdminController.php';
require_once __DIR__ . '/../src/Models/AuditLog.php';
SessionService::start(SessionService::ADMIN_SESSION);
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$ctrl = new AdminController();
$data = $ctrl->dashboard();
>>>>>>> 4b4892c0a36933c726154fb629a76e5be16d9c40
require __DIR__ . '/../views/admin/dashboard.php';