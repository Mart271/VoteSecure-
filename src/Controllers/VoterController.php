<?php
// src/Controllers/VoterController.php

require_once __DIR__ . '/../Services/AuthService.php';
require_once __DIR__ . '/../Models/Voter.php';
require_once __DIR__ . '/../Models/Election.php';
require_once __DIR__ . '/../Models/Position.php';
require_once __DIR__ . '/../Models/Candidate.php';
require_once __DIR__ . '/../Models/Ballot.php';
require_once __DIR__ . '/../Models/AuditLog.php';

class VoterController
{
    private AuthService $auth;
    private Voter       $voterModel;
    private Election    $electionModel;
    private Position    $positionModel;
    private Candidate   $candidateModel;
    private Ballot      $ballotModel;
    private AuditLog    $auditModel;

    public function __construct()
    {
        $this->auth           = new AuthService();
        $this->voterModel     = new Voter();
        $this->electionModel  = new Election();
        $this->positionModel  = new Position();
        $this->candidateModel = new Candidate();
        $this->ballotModel    = new Ballot();
        $this->auditModel     = new AuditLog();
        $this->auth->requireRole('voter');
    }

    public function portal(): array
    {
        $userId      = $this->auth->currentUser()['user_id'];
        $registered  = $this->voterModel->getByUser($userId);
        $registeredMap = [];

        foreach ($registered as $row) {
            $row['is_registered'] = true;
            $registeredMap[$row['election_id']] = $row;
        }

        $activeElections = $this->electionModel->getActiveElections();
        foreach ($activeElections as $election) {
            if (!isset($registeredMap[$election['election_id']])) {
                $registeredMap[$election['election_id']] = [
                    'election_id'    => $election['election_id'],
                    'election_title' => $election['title'],
                    'election_status'=> $election['status'],
                    'start_datetime' => $election['start_datetime'],
                    'end_datetime'   => $election['end_datetime'],
                    'has_voted'      => false,
                    'voter_code'     => null,
                    'is_registered'  => false,
                ];
            }
        }

        // Preserve ordering with active elections first, then registered elections
        $elections = array_values($registeredMap);

        return ['elections' => $elections, 'user' => $this->auth->currentUser()];
    }

    public function getBallot(int $electionId): array
    {
        $userId  = $this->auth->currentUser()['user_id'];
        $voter   = $this->voterModel->findByUserAndElection($userId, $electionId);

        if (!$voter)          return ['error' => 'You are not registered for this election.'];
        if ($voter['has_voted']) return ['error' => 'You have already cast your ballot.'];

        $election  = $this->electionModel->findById($electionId);
        if (!$election || $election['status'] !== 'active') {
            return ['error' => 'This election is not currently active.'];
        }

        $positions  = $this->positionModel->getByElection($electionId);
        $candidates = $this->candidateModel->getByElection($electionId);

        return compact('voter', 'election', 'positions', 'candidates');
    }

    public function castBallot(int $electionId, array $choices): array
    {
        $userId = $this->auth->currentUser()['user_id'];
        $voter  = $this->voterModel->findByUserAndElection($userId, $electionId);

        if (!$voter)             return ['success' => false, 'message' => 'Voter registration not found.'];
        if ($voter['has_voted']) return ['success' => false, 'message' => 'You have already voted.'];

        $election = $this->electionModel->findById($electionId);
        if (!$election || $election['status'] !== 'active') {
            return ['success' => false, 'message' => 'Election is not active.'];
        }

        try {
            $ip       = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $ballotId = $this->ballotModel->submitBallot($voter['voter_id'], $electionId, $choices, $ip);
            $this->voterModel->markVoted($voter['voter_id']);

            $this->auditModel->log(
                'BALLOT_CAST',
                "Voter {$voter['voter_code']} cast ballot #{$ballotId} in Election #{$electionId}.",
                $userId
            );

            return ['success' => true, 'ballot_id' => $ballotId, 'voter_code' => $voter['voter_code']];
        } catch (Throwable $e) {
            error_log('Ballot submission error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to submit ballot. Please try again.'];
        }
    }

    public function getResults(int $electionId): array
    {
        $election   = $this->electionModel->findById($electionId);
        if (!$election || !in_array($election['status'], ['published', 'closed'], true)) {
            return ['error' => 'Results are not available for this election.'];
        }
        $results  = (new Candidate())->getVoteCounts($electionId);
        $turnout  = $this->voterModel->getTurnout($electionId);
        $positions = $this->positionModel->getByElection($electionId);
        return compact('election', 'results', 'turnout', 'positions');
    }

    public function getMyBallot(int $electionId): array
    {
        $userId = $this->auth->currentUser()['user_id'];
        $voter  = $this->voterModel->findByUserAndElection($userId, $electionId);

        if (!$voter) {
            return ['error' => 'You are not registered for this election.'];
        }
        if (!$voter['has_voted']) {
            return ['error' => 'You have not voted in this election yet.'];
        }

        $ballot = $this->ballotModel->findByVoter($voter['voter_id']);
        if (!$ballot) {
            return ['error' => 'Your ballot could not be found.'];
        }

        $choices  = $this->ballotModel->getChoicesForBallot($ballot['ballot_id']);
        $election = $this->electionModel->findById($electionId);
        return compact('election', 'voter', 'ballot', 'choices');
    }
}