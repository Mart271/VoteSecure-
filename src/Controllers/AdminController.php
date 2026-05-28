<?php
// src/Controllers/AdminController.php

require_once __DIR__ . '/../Services/AuthService.php';
require_once __DIR__ . '/../Models/Election.php';
require_once __DIR__ . '/../Models/Position.php';
require_once __DIR__ . '/../Models/Candidate.php';
require_once __DIR__ . '/../Models/Voter.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/AuditLog.php';
require_once __DIR__ . '/../Database.php';

class AdminController
{
    private AuthService $auth;
    private Election    $electionModel;
    private Position    $positionModel;
    private Candidate   $candidateModel;
    private Voter       $voterModel;
    private User        $userModel;
    private AuditLog    $auditModel;
    private Database    $db;

    public function __construct()
    {
        $this->auth           = new AuthService();
        $this->electionModel  = new Election();
        $this->positionModel  = new Position();
        $this->candidateModel = new Candidate();
        $this->voterModel     = new Voter();
        $this->userModel      = new User();
        $this->auditModel     = new AuditLog();
        $this->db             = Database::getInstance();
        $this->auth->requireRole('election_admin', 'system_admin');
    }

    // ── Dashboard ──────────────────────────────────
    public function dashboard(): array
    {
        $stats        = $this->electionModel->getDashboardStats();
        $voterStats   = $this->voterModel->getAllVoterStats();
        $elections    = $this->electionModel->getAllWithStats();
        $recentLogs   = $this->auditModel->getRecent(8);

        $totalVoters  = (int) $voterStats['total_voters'];
        $totalVoted   = (int) $voterStats['total_voted'];

        return [
            'stats'        => $stats,
            'total_voters' => $totalVoters,
            'avg_turnout'  => $totalVoters > 0 ? round($totalVoted / $totalVoters * 100, 1) : 0,
            'elections'    => $elections,
            'recent_logs'  => $recentLogs,
        ];
    }

    // ── Elections ──────────────────────────────────
    public function listElections(): array
    {
        return $this->electionModel->getAllWithStats();
    }

    public function createElection(array $data): array
    {
        $required = ['title', 'start_datetime', 'end_datetime'];
        foreach ($required as $f) {
            if (empty($data[$f])) return ['success' => false, 'message' => "Field '{$f}' is required."];
        }

        $id = $this->electionModel->insert([
            'title'          => htmlspecialchars($data['title']),
            'description'    => htmlspecialchars($data['description'] ?? ''),
            'start_datetime' => $data['start_datetime'],
            'end_datetime'   => $data['end_datetime'],
            'status'         => $data['status'] ?? 'draft',
            'created_by'     => $this->auth->currentUser()['user_id'],
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        $this->auditModel->log('ELECTION_CREATED', "Election \"{$data['title']}\" created.", $this->auth->currentUser()['user_id']);
        return ['success' => true, 'id' => $id];
    }

    public function updateElectionStatus(int $id, string $status): array
    {
        $allowed = ['draft', 'active', 'closed', 'published'];
        if (!in_array($status, $allowed, true)) return ['success' => false, 'message' => 'Invalid status.'];

        $this->electionModel->setStatus($id, $status);
        $this->auditModel->log('ELECTION_STATUS_CHANGED', "Election #{$id} set to '{$status}'.", $this->auth->currentUser()['user_id']);
        return ['success' => true];
    }

    public function deleteElection(int $id): array
    {
        $this->electionModel->delete($id);
        $this->auditModel->log('ELECTION_DELETED', "Election #{$id} deleted.", $this->auth->currentUser()['user_id']);
        return ['success' => true];
    }

    // ── Positions ──────────────────────────────────
    public function addPosition(array $data): array
    {
        if (empty($data['election_id']) || empty($data['position_name'])) {
            return ['success' => false, 'message' => 'Election and position name are required.'];
        }

        $id = $this->positionModel->insert([
            'election_id'   => (int) $data['election_id'],
            'position_name' => htmlspecialchars($data['position_name']),
            'max_votes'     => (int) ($data['max_votes'] ?? 1),
            'display_order' => (int) ($data['display_order'] ?? 1),
        ]);

        $this->auditModel->log('POSITION_ADDED', "Position \"{$data['position_name']}\" added to Election #{$data['election_id']}.", $this->auth->currentUser()['user_id']);
        return ['success' => true, 'id' => $id];
    }

    // ── Candidates ────────────────────────────────
    public function listCandidates(): array
    {
        return $this->candidateModel->getAllWithDetails();
    }

    public function addCandidate(array $data): array
    {
        if (empty($data['position_id']) || empty($data['full_name'])) {
            return ['success' => false, 'message' => 'Position and candidate name are required.'];
        }

        $id = $this->candidateModel->insert([
            'position_id'      => (int) $data['position_id'],
            'full_name'        => htmlspecialchars($data['full_name']),
            'party_affiliation'=> htmlspecialchars($data['party_affiliation'] ?? ''),
            'platform'         => htmlspecialchars($data['platform'] ?? ''),
            'photo_url'        => $data['photo_url'] ?? null,
            'is_disqualified'  => 0,
        ]);

        $this->auditModel->log('CANDIDATE_ADDED', "Candidate \"{$data['full_name']}\" registered.", $this->auth->currentUser()['user_id']);
        return ['success' => true, 'id' => $id];
    }

    public function disqualifyCandidate(int $id): array
    {
        $this->candidateModel->update($id, ['is_disqualified' => 1]);
        $this->auditModel->log('CANDIDATE_DISQUALIFIED', "Candidate #{$id} disqualified.", $this->auth->currentUser()['user_id']);
        return ['success' => true];
    }

    // ── Voters ────────────────────────────────────
    public function listVoters(): array
    {
        // JOIN users + elections so view always has full_name, username, election_title
        return $this->db->query("
            SELECT v.*,
                   u.full_name,
                   u.username,
                   u.email,
                   e.title      AS election_title,
                   e.election_id
            FROM voters v
            JOIN users     u ON u.user_id     = v.user_id
            JOIN elections e ON e.election_id = v.election_id
            ORDER BY v.voter_id DESC
        ");
    }

    public function registerVoter(array $data): array
    {
        if (empty($data['election_id']) || empty($data['username']) || empty($data['full_name'])) {
            return ['success' => false, 'message' => 'Election, username, and full name are required.'];
        }

        $electionId = (int) $data['election_id'];

        // Upsert user
        $user = $this->userModel->findByUsername($data['username']);
        if (!$user) {
            $userId = $this->userModel->createUser([
                'username'   => $data['username'],
                'email'      => $data['email'] ?? $data['username'] . '@votesecure.local',
                'password'   => $data['password'] ?? 'voter123',
                'full_name'  => htmlspecialchars($data['full_name']),
                'role'       => 'voter',
                'is_active'  => 1,
            ]);
        } else {
            $userId = $user['user_id'];
        }

        // Check duplicate
        $existing = $this->voterModel->findByUserAndElection($userId, $electionId);
        if ($existing) return ['success' => false, 'message' => 'Voter is already registered for this election.'];

        $voterCode = $this->voterModel->generateVoterCode($electionId);
        $this->voterModel->insert([
            'user_id'     => $userId,
            'election_id' => $electionId,
            'voter_code'  => $voterCode,
            'has_voted'   => 0,
            'voted_at'    => null,
        ]);

        $this->auditModel->log('VOTER_REGISTERED', "Voter \"{$data['full_name']}\" registered. Code: {$voterCode}", $this->auth->currentUser()['user_id']);
        return ['success' => true, 'voter_code' => $voterCode];
    }

    // ── Audit Log ─────────────────────────────────
    public function getAuditLog(int $limit = 100): array
    {
        return $this->auditModel->getRecent($limit);
    }

    // ── Results ───────────────────────────────────
    public function getResults(int $electionId): array
    {
        $election  = $this->electionModel->getWithStats($electionId);
        $results   = $this->candidateModel->getVoteCounts($electionId);
        $turnout   = $this->voterModel->getTurnout($electionId);
        $positions = $this->positionModel->getByElection($electionId);

        return compact('election', 'results', 'turnout', 'positions');
    }
}