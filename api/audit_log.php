<?php
// admin/audit_log.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Services/SessionService.php';
require_once __DIR__ . '/../src/Controllers/AdminController.php';
require_once __DIR__ . '/../src/Models/AuditLog.php';
SessionService::start(SessionService::ADMIN_SESSION);

$ctrl = new AdminController();
$logs = $ctrl->getAuditLog(200);
require __DIR__ . '/../views/admin/audit_log.php';