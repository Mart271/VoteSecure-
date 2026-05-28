<?php
// src/Services/SessionService.php

class SessionService
{
    /** Login page flash messages and CSRF (not authenticated). */
    public const DEFAULT_SESSION = 'VOTESECURE_DEFAULT';

    /** Administrator authenticated session. */
    public const ADMIN_SESSION   = 'VOTESECURE_ADMIN';

    /** Voter authenticated session. */
    public const VOTER_SESSION   = 'VOTESECURE_VOTER';

    public static function start(string $sessionName = self::DEFAULT_SESSION): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            if (session_name() === $sessionName) {
                return;
            }
            session_write_close();
        }
        session_name($sessionName);
        session_start();
    }

    public static function switchTo(string $sessionName): void
    {
        self::start($sessionName);
    }

    public static function destroy(string $sessionName): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            session_write_close();
        }
        session_name($sessionName);
        session_start();
        $_SESSION = [];
        session_destroy();
        setcookie($sessionName, '', time() - 3600, '/');
    }

    /** Read logged-in state from a session without leaving the caller on that session. */
    public static function peekLoggedIn(string $sessionName): ?array
    {
        $current = session_status() === PHP_SESSION_ACTIVE ? session_name() : null;
        if ($current !== $sessionName) {
            self::switchTo($sessionName);
        }
        $data = (!empty($_SESSION['logged_in']) && !empty($_SESSION['user_id']))
            ? [
                'user_id'   => $_SESSION['user_id'],
                'username'  => $_SESSION['username'] ?? '',
                'full_name' => $_SESSION['full_name'] ?? '',
                'role'      => $_SESSION['role'] ?? '',
            ]
            : null;
        if ($current !== null && $current !== $sessionName) {
            self::switchTo($current);
        }
        return $data;
    }
}
