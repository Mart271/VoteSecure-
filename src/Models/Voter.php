<?php
// src/Models/Voter.php

require_once __DIR__ . '/Model.php';

class Voter extends Model
{
    protected string $table = 'voters';
    protected string $pk    = 'voter_id';

    public function findByUserAndElection(int $userId, int $electionId): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM voters WHERE user_id = ? AND election_id = ?",
            [$userId, $electionId]
        );
    }

    public function getByElection(int $electionId): array
    {
        return $this->db->query("
            SELECT v.*, u.full_name, u.username, u.email
            FROM voters v
            JOIN users u ON u.user_id = v.user_id
            WHERE v.election_id = ?
            ORDER BY u.full_name
        ", [$electionId]);
    }

    public function getByUser(int $userId): array
    {
        return $this->db->query("
            SELECT v.*, e.title AS election_title, e.status AS election_status,
                   e.start_datetime, e.end_datetime
            FROM voters v
            JOIN elections e ON e.election_id = v.election_id
            WHERE v.user_id = ?
            ORDER BY e.start_datetime DESC
        ", [$userId]);
    }

    public function generateVoterCode(int $electionId): string
    {
        $year   = date('Y');
        $seq    = $this->count('election_id = ?', [$electionId]) + 1;
        return sprintf('VC-%d-%s-%03d', $electionId, $year, $seq);
    }

    public function markVoted(int $voterId): void
    {
        $this->update($voterId, [
            'has_voted' => 1,
            'voted_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    public function getTurnout(int $electionId): array
    {
        $row = $this->db->queryOne("
            SELECT COUNT(*) AS total,
                   SUM(has_voted) AS voted
            FROM voters WHERE election_id = ?
        ", [$electionId]);
        $total  = (int) ($row['total'] ?? 0);
        $voted  = (int) ($row['voted'] ?? 0);
        return [
            'total'   => $total,
            'voted'   => $voted,
            'pct'     => $total > 0 ? round($voted / $total * 100, 1) : 0,
        ];
    }

    public function getAllVoterStats(): array
    {
        return $this->db->queryOne("
            SELECT COUNT(*) AS total_voters,
                   SUM(has_voted) AS total_voted
            FROM voters
        ") ?? ['total_voters' => 0, 'total_voted' => 0];
    }
}