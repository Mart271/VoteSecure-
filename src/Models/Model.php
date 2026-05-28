<?php
// src/Models/Model.php

require_once __DIR__ . '/../Database.php';

abstract class Model
{
    protected Database $db;
    protected string   $table  = '';
    protected string   $pk     = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM {$this->table} WHERE {$this->pk} = ?",
            [$id]
        );
    }

    public function findAll(string $where = '', array $params = [], string $order = ''): array
    {
        $sql = "SELECT * FROM {$this->table}";
        if ($where) $sql .= " WHERE {$where}";
        if ($order) $sql .= " ORDER BY {$order}";
        return $this->db->query($sql, $params);
    }

    public function insert(array $data): int
    {
        $cols   = implode(', ', array_keys($data));
        $marks  = implode(', ', array_fill(0, count($data), '?'));
        $this->db->execute(
            "INSERT INTO {$this->table} ({$cols}) VALUES ({$marks})",
            array_values($data)
        );
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): int
    {
        $sets = implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($data)));
        return $this->db->execute(
            "UPDATE {$this->table} SET {$sets} WHERE {$this->pk} = ?",
            [...array_values($data), $id]
        );
    }

    public function delete(int $id): int
    {
        return $this->db->execute(
            "DELETE FROM {$this->table} WHERE {$this->pk} = ?",
            [$id]
        );
    }

    public function count(string $where = '', array $params = []): int
    {
        $sql = "SELECT COUNT(*) AS cnt FROM {$this->table}";
        if ($where) $sql .= " WHERE {$where}";
        $row = $this->db->queryOne($sql, $params);
        return (int) ($row['cnt'] ?? 0);
    }
}