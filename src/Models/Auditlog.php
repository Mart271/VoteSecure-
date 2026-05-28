<?php
// src/Models/AuditLog.php

require_once __DIR__ . '/Model.php';

class AuditLog extends Model
{
    protected string $table = 'audit_logs';
    protected string $pk    = 'log_id';

    public function log(string $action, string $description, ?int $userId = null, ?string $ip = null): void
    {
        $this->insert([
            'user_id'     => $userId,
            'action'      => $action,
            'description' => $description,
            'ip_address'  => $ip ?? ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    public function getRecent(int $limit = 50): array
    {
        return $this->db->query("
            SELECT al.*, u.full_name, u.username
            FROM audit_logs al
            LEFT JOIN users u ON u.user_id = al.user_id
            ORDER BY al.created_at DESC
            LIMIT ?
        ", [$limit]);
    }

    public function getIconForAction(string $action): string
    {
        return match (true) {
            str_contains($action, 'LOGIN')     => '🔐',
            str_contains($action, 'BALLOT')    => '🗳️',
            str_contains($action, 'ELECTION')  => '📋',
            str_contains($action, 'CANDIDATE') => '👤',
            str_contains($action, 'VOTER')     => '👥',
            str_contains($action, 'RESULT')    => '📊',
            str_contains($action, 'AI')        => '🤖',
            default                            => '📝',
        };
    }
}