<?php
// src/Models/Ballot.php

require_once __DIR__ . '/Model.php';

class Ballot extends Model
{
    protected string $table = 'ballots';
    protected string $pk    = 'ballot_id';

    public function findByVoter(int $voterId): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM ballots WHERE voter_id = ?",
            [$voterId]
        );
    }

    /**
     * Atomically submit a ballot with all choices inside a transaction.
     * Returns the new ballot_id or throws on failure.
     */
    public function submitBallot(int $voterId, int $electionId, array $choiceMap, string $ip): int
    {
        $this->db->beginTransaction();
        try {
            // Insert ballot header
            $ballotId = $this->insert([
                'voter_id'     => $voterId,
                'election_id'  => $electionId,
                'submitted_at' => date('Y-m-d H:i:s'),
                'ip_address'   => $ip,
            ]);

            // Insert each choice  [positionId => candidateId]
            foreach ($choiceMap as $candidateId) {
                if (!$candidateId) continue;
                $this->db->execute(
                    "INSERT INTO ballot_choices (ballot_id, candidate_id) VALUES (?, ?)",
                    [$ballotId, (int) $candidateId]
                );
            }

            $this->db->commit();
            return $ballotId;
        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function getChoicesForBallot(int $ballotId): array
    {
        return $this->db->query("
            SELECT bc.*, c.full_name, c.party_affiliation, p.position_name
            FROM ballot_choices bc
            JOIN candidates c ON c.candidate_id = bc.candidate_id
            JOIN positions  p ON p.position_id  = c.position_id
            WHERE bc.ballot_id = ?
        ", [$ballotId]);
    }

    public function getCountByElection(int $electionId): int
    {
        $row = $this->db->queryOne(
            "SELECT COUNT(*) AS cnt FROM ballots WHERE election_id = ?",
            [$electionId]
        );
        return (int) ($row['cnt'] ?? 0);
    }
}