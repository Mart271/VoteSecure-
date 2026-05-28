<?php
// admin/ai_analytics.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Services/SessionService.php';
require_once __DIR__ . '/../src/Services/AuthService.php';
require_once __DIR__ . '/../src/Models/Election.php';
SessionService::start(SessionService::ADMIN_SESSION);
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$auth = new AuthService();
$auth->requireRole('election_admin', 'system_admin');

$elections  = (new Election())->getAllWithStats();
$selectedId = (int) ($_GET['election_id'] ?? 0);

require __DIR__ . '/../views/admin/ai_analytics.php';