<?php
// src/Services/AuthService.php

require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/AuditLog.php';

class AuthService
{
    private User     $userModel;
    private AuditLog $auditModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel  = new User();
        $this->auditModel = new AuditLog();
    }

    public function login(string $username, string $password): array
    {
        $user = $this->userModel->findByUsername($username);

        if (!$user || !$this->userModel->verifyPassword($password, $user['password_hash'])) {
            $this->auditModel->log('LOGIN_FAILED', "Failed login attempt for username: {$username}");
            return ['success' => false, 'message' => 'Invalid username or password.'];
        }

        session_regenerate_id(true);

        $_SESSION['user_id']   = $user['user_id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role']      = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_at']  = time();

        $this->userModel->updateLastLogin($user['user_id']);
        $this->auditModel->log('LOGIN_SUCCESS', "User {$username} logged in.", $user['user_id']);

        return ['success' => true, 'role' => $user['role']];
    }

    public function logout(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->auditModel->log(
                'LOGOUT',
                "User {$_SESSION['username']} logged out.",
                $_SESSION['user_id']
            );
        }
        $_SESSION = [];
        session_destroy();
    }

    public function isLoggedIn(): bool
    {
        return !empty($_SESSION['logged_in']) && !empty($_SESSION['user_id']);
    }

    public function requireLogin(string $loginRole = 'voter'): void
    {
        if (!$this->isLoggedIn()) {
            header('Location: ' . APP_URL . '/index.php?role=' . urlencode($loginRole));
            exit;
        }
    }

    public function requireRole(string ...$roles): void
    {
        $loginRole = in_array('voter', $roles, true) ? 'voter' : 'admin';
        $this->requireLogin($loginRole);

        if (!in_array($_SESSION['role'] ?? '', $roles, true)) {
            $this->redirectToOwnPortal();
        }
    }

    private function redirectToOwnPortal(): void
    {
        if (in_array($_SESSION['role'] ?? '', ['system_admin', 'election_admin'], true)) {
            header('Location: ' . APP_URL . '/admin/dashboard.php');
        } else {
            header('Location: ' . APP_URL . '/voter/portal.php');
        }
        exit;
    }

    public function currentUser(): ?array
    {
        if (!$this->isLoggedIn()) return null;
        return [
            'user_id'   => $_SESSION['user_id'],
            'username'  => $_SESSION['username'],
            'full_name' => $_SESSION['full_name'],
            'role'      => $_SESSION['role'],
        ];
    }

    public function isAdmin(): bool
    {
        return in_array($_SESSION['role'] ?? '', ['system_admin', 'election_admin'], true);
    }

    public function isVoter(): bool
    {
        return ($_SESSION['role'] ?? '') === 'voter';
    }
}
