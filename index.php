<?php
// index.php — Login entry point

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Services/SessionService.php';
require_once __DIR__ . '/src/Services/AuthService.php';
require_once __DIR__ . '/src/Controllers/AuthController.php';

SessionService::start(SessionService::DEFAULT_SESSION);

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$controller = new AuthController();
$action     = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'login') {
    // Validate CSRF
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $_SESSION['login_error'] = 'Invalid request. Please try again.';
        header('Location: ' . APP_URL . '/index.php');
        exit;
    }
    $controller->handleLogin($_POST['login_as'] ?? 'voter');
} elseif ($action === 'logout') {
    $controller->handleLogout();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'register') {
    // NOTE: basic CSRF protection for register can be implemented by using
    // the same csrf_token as the login form on the default session.
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $_SESSION['login_error'] = 'Invalid request. Please try again.';
        header('Location: ' . APP_URL . '/index.php?action=register');
        exit;
    }
    $controller->handleRegister();
} elseif ($action === 'register') {
    $controller->showRegister();
} else {
    $controller->showLogin();
}