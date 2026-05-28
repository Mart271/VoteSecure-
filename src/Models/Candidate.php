<?php
// src/Models/Candidate.php

require_once __DIR__ . '/Model.php';

class Candidate extends Model
{
    protected string $table = 'candidates';
    protected string $pk    = 'candidate_id';

    public function getByPosition(int $positionId): array
    {
        return $this->findAll(
            'position_id = ? AND is_disqualified = 0',
            [$positionId],
            'full_name ASC'
        );
    }

    public function getByElection(int $electionId): array
    {
        return $this->db->query("
            SELECT c.*, p.position_name, p.election_id
            FROM candidates c
            JOIN positions p ON p.position_id = c.position_id
            WHERE p.election_id = ? AND c.is_disqualified = 0
            ORDER BY p.display_order, c.full_name
        ", [$electionId]);
    }

    public function getAllWithDetails(): array
    {
        return $this->db->query("
            SELECT c.*, p.position_name, e.title AS election_title, e.election_id
            FROM candidates c
            JOIN positions p ON p.position_id   = c.position_id
            JOIN elections e ON e.election_id   = p.election_id
            ORDER BY e.title, p.display_order, c.full_name
        ");
    }

    public function getVoteCounts(int $electionId): array
    {
        return $this->db->query("
            SELECT c.*,
                   p.position_name,
                   p.position_id,
                   COUNT(bc.choice_id) AS vote_count
            FROM candidates c
            JOIN positions p     ON p.position_id  = c.position_id
            LEFT JOIN ballot_choices bc ON bc.candidate_id = c.candidate_id
            LEFT JOIN ballots b         ON b.ballot_id     = bc.ballot_id
                                       AND b.election_id   = ?
            WHERE p.election_id = ? AND c.is_disqualified = 0
            GROUP BY c.candidate_id
            ORDER BY p.display_order, vote_count DESC
        ", [$electionId, $electionId]);
    }
}