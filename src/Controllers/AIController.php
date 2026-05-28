<?php
// src/Controllers/AIController.php

require_once __DIR__ . '/../Services/AuthService.php';
require_once __DIR__ . '/../Services/QwenAIService.php';

class AIController
{
    private AuthService    $auth;
    private QwenAIService  $ai;

    public function __construct()
    {
        $this->auth = new AuthService();
        $this->ai   = new QwenAIService();
        $this->auth->requireRole('election_admin', 'system_admin');
    }

    public function analyze(int $electionId): void
    {
        $userId = $this->auth->currentUser()['user_id'];
        $result = $this->ai->analyzeElection($electionId, $userId);
        $this->jsonResponse($result);
    }

    public function detectFraud(int $electionId): void
    {
        $userId = $this->auth->currentUser()['user_id'];
        $result = $this->ai->detectFraud($electionId, $userId);
        $this->jsonResponse($result);
    }

    public function predictTurnout(int $electionId): void
    {
        $userId = $this->auth->currentUser()['user_id'];
        $result = $this->ai->predictTurnout($electionId, $userId);
        $this->jsonResponse($result);
    }

    public function resultsSummary(int $electionId): void
    {
        $userId = $this->auth->currentUser()['user_id'];
        $result = $this->ai->generateResultsSummary($electionId, $userId);
        $this->jsonResponse($result);
    }

    private function jsonResponse(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}