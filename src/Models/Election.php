<?php
// src/Models/Election.php

require_once __DIR__ . '/Model.php';

class Election extends Model
{
    protected string $table = 'elections';
    protected string $pk    = 'election_id';

    public function getAllWithStats(): array
    {
        return $this->db->query("
            SELECT e.*,
                   u.full_name                                          AS creator_name,
                   COUNT(DISTINCT p.position_id)                        AS position_count,
                   COUNT(DISTINCT c.candidate_id)                       AS candidate_count,
                   COUNT(DISTINCT v.voter_id)                           AS voter_count,
                   SUM(CASE WHEN v.has_voted = 1 THEN 1 ELSE 0 END)    AS voted_count
            FROM elections e
            LEFT JOIN users       u ON e.created_by   = u.user_id
            LEFT JOIN positions   p ON p.election_id  = e.election_id
            LEFT JOIN candidates  c ON c.position_id  = p.position_id
            LEFT JOIN voters      v ON v.election_id  = e.election_id
            GROUP BY e.election_id
            ORDER BY e.created_at DESC
        ");
    }

    public function getWithStats(int $id): ?array
    {
        return $this->db->queryOne("
            SELECT e.*,
                   u.full_name                                          AS creator_name,
                   COUNT(DISTINCT p.position_id)                        AS position_count,
                   COUNT(DISTINCT c.candidate_id)                       AS candidate_count,
                   COUNT(DISTINCT v.voter_id)                           AS voter_count,
                   SUM(CASE WHEN v.has_voted = 1 THEN 1 ELSE 0 END)    AS voted_count
            FROM elections e
            LEFT JOIN users       u ON e.created_by   = u.user_id
            LEFT JOIN positions   p ON p.election_id  = e.election_id
            LEFT JOIN candidates  c ON c.position_id  = p.position_id
            LEFT JOIN voters      v ON v.election_id  = e.election_id
            WHERE e.election_id = ?
            GROUP BY e.election_id
        ", [$id]);
    }

    public function getActiveElections(): array
    {
        return $this->findAll("status = 'active'", [], 'start_datetime ASC');
    }

    public function getPublishedElections(): array
    {
        return $this->findAll("status IN ('published','closed')", [], 'end_datetime DESC');
    }

    public function setStatus(int $id, string $status): int
    {
        return $this->update($id, ['status' => $status]);
    }

    public function getDashboardStats(): array
    {
        $row = $this->db->queryOne("
            SELECT
                COUNT(*)                                                  AS total_elections,
                SUM(CASE WHEN status = 'active'    THEN 1 ELSE 0 END)   AS active_elections,
                SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END)   AS published_elections
            FROM elections
        ");
        return $row ?? ['total_elections' => 0, 'active_elections' => 0, 'published_elections' => 0];
    }
}