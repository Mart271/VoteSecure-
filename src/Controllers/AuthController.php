<?php
// src/Controllers/AuthController.php

require_once __DIR__ . '/../Services/SessionService.php';
require_once __DIR__ . '/../Services/AuthService.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Database.php';

class AuthController
{
    private AuthService $auth;

    public function __construct()
    {
        $this->auth = new AuthService();
    }

    public function showLogin(): void
    {
        if ($this->auth->isLoggedIn()) {
            $this->redirectByRole();
        }
        require __DIR__ . '/../../views/auth/login.php';
    }

    public function handleLogin(string $loginAs = 'voter'): void
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password']           ?? '';

        if (!$username || !$password) {
            $_SESSION['login_error'] = 'Please enter both username and password.';
            header('Location: ' . APP_URL . '/index.php');
            exit;
        }

        $defaultSessionName = session_name();
        session_write_close();

        if ($loginAs === 'admin') {
            SessionService::start(SessionService::ADMIN_SESSION);
        } else {
            SessionService::start(SessionService::VOTER_SESSION);
        }

        $this->auth = new AuthService();
        $result = $this->auth->login($username, $password);

        if ($result['success']) {
            $this->redirectByRole();
        } else {
            SessionService::switchTo($defaultSessionName);
            $_SESSION['login_error'] = $result['message'];
            header('Location: ' . APP_URL . '/index.php');
            exit;
        }
    }

    public function handleLogout(): void
    {
        $this->auth->logout();
        header('Location: ' . APP_URL . '/index.php');
        exit;
    }

    private function redirectByRole(): void
    {
        $role = $_SESSION['role'] ?? 'voter';
        if (in_array($role, ['system_admin', 'election_admin'], true)) {
            header('Location: ' . APP_URL . '/admin/dashboard.php');
        } else {
            header('Location: ' . APP_URL . '/voter/portal.php');
        }
        exit;
    }

    public function showRegister(): void
    {
        if ($this->auth->isLoggedIn()) {
            $this->redirectByRole();
        }
        require __DIR__ . '/../../views/auth/register.php';
    }

    public function handleRegister(): void
    {
        $displayName = trim($_POST['display_name'] ?? '');
        $schoolEmail = trim($_POST['school_email'] ?? '');
        $department  = trim($_POST['department'] ?? '');
        $section     = trim($_POST['section'] ?? '');
        $password    = $_POST['password'] ?? '';
        $confirm     = $_POST['confirm_password'] ?? '';

        if (!$schoolEmail || !$password || !$confirm) {
            $_SESSION['login_error'] = 'Please fill required fields.';
            header('Location: ' . APP_URL . '/index.php?action=register');
            exit;
        }
        if (!filter_var($schoolEmail, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['login_error'] = 'Please provide a valid school email.';
            header('Location: ' . APP_URL . '/index.php?action=register');
            exit;
        }
        if ($password !== $confirm) {
            $_SESSION['login_error'] = 'Passwords do not match.';
            header('Location: ' . APP_URL . '/index.php?action=register');
            exit;
        }

        $userModel = new User();

        // prevent duplicate email
        if ($userModel->findByEmail($schoolEmail)) {
            $_SESSION['login_error'] = 'An account with that email already exists.';
            header('Location: ' . APP_URL . '/index.php?action=register');
            exit;
        }

        // create a safe username from email local part and ensure uniqueness
        $base = preg_replace('/[^a-z0-9_.-]/i', '', strstr($schoolEmail, '@', true) ?: $schoolEmail);
        $username = $base;
        $i = 1;
        while ($userModel->findByUsername($username)) {
            $username = $base . $i;
            $i++;
        }

        // Determine which columns exist on users table to avoid SQL errors
        $db = Database::getInstance();
        $cols = array_map(fn($r) => $r['Field'], $db->query('SHOW COLUMNS FROM users'));

        $data = [
            'username'  => $username,
            'email'     => $schoolEmail,
            'full_name' => $displayName ?: $username,
            'role'      => 'voter',
            'password'  => $password,
        ];

        if (in_array('school_email', $cols, true)) $data['school_email'] = $schoolEmail;
        if (in_array('department', $cols, true))    $data['department']   = $department;
        if (in_array('section', $cols, true))       $data['section']      = $section;

        try {
            $userId = $userModel->createUser($data);
            $_SESSION['login_success'] = 'Account created successfully. You may now sign in.';
            header('Location: ' . APP_URL . '/index.php');
            exit;
        } catch (Throwable $e) {
            error_log('Registration error: ' . $e->getMessage());
            $_SESSION['login_error'] = 'Failed to create account. Please contact support.';
            header('Location: ' . APP_URL . '/index.php?action=register');
            exit;
        }
    }
}