<?php
// src/Models/Position.php

require_once __DIR__ . '/Model.php';

class Position extends Model
{
    protected string $table = 'positions';
    protected string $pk    = 'position_id';

    public function getByElection(int $electionId): array
    {
        return $this->findAll(
            'election_id = ?',
            [$electionId],
            'display_order ASC'
        );
    }

    public function getWithCandidates(int $electionId): array
    {
        return $this->db->query("
            SELECT p.*,
                   COUNT(c.candidate_id) AS candidate_count
            FROM positions p
            LEFT JOIN candidates c ON c.position_id = p.position_id
            WHERE p.election_id = ?
            GROUP BY p.position_id
            ORDER BY p.display_order
        ", [$electionId]);
    }
}