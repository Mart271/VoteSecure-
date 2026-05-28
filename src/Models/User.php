<?php
// src/Models/User.php

require_once __DIR__ . '/Model.php';

class User extends Model
{
    protected string $table = 'users';
    protected string $pk    = 'user_id';

    public function findByUsername(string $username): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM users WHERE username = ? AND is_active = 1",
            [$username]
        );
    }

    public function findByEmail(string $email): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM users WHERE email = ? AND is_active = 1",
            [$email]
        );
    }

    public function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    public function createUser(array $data): int
    {
        $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        unset($data['password']);
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }

    public function updateLastLogin(int $userId): void
    {
        $this->update($userId, ['last_login_at' => date('Y-m-d H:i:s')]);
    }

    public function updatePassword(int $userId, string $newPassword): int
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        return $this->update($userId, ['password_hash' => $hash]);
    }

    public function getVoters(): array
    {
        return $this->findAll("role = 'voter'", [], 'full_name ASC');
    }
}